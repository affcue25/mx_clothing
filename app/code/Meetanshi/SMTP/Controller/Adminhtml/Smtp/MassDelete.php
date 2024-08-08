<?php
namespace Meetanshi\SMTP\Controller\Adminhtml\Smtp;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Meetanshi\SMTP\Model\ResourceModel\Logs\CollectionFactory;


/**
 * Class MassDelete
 * @package Meetanshi\SMTP\Controller\Adminhtml\Smtp
 */
class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $emailLog;

    /**
     * MassDelete constructor.
     * @param Filter $filter
     * @param Action\Context $context
     * @param CollectionFactory $emailLog
     */
    public function __construct(
        Filter $filter,
        Action\Context $context,
        CollectionFactory $emailLog
    ) {
        $this->filter = $filter;
        $this->emailLog = $emailLog;

        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $collection = $this->filter->getCollection($this->emailLog->create());
            $deleted = 0;
            foreach ($collection->getItems() as $item) {
                $item->delete();
                $deleted++;
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. %1', $e->getMessage())
            );
            $this->_redirect('mt_smtp/smtp/log');

            return;
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 mail log(s) have been deleted.', $deleted)
        );

        return $resultRedirect->setPath('mt_smtp/smtp/log');
    }
}
