<?php
namespace Meetanshi\SMTP\Controller\Adminhtml\Smtp;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Meetanshi\SMTP\Model\LogsFactory as ELogs;

/**
 * Class Delete
 * @package Meetanshi\SMTP\Controller\Adminhtml\Smtp
 */
class Delete extends Action
{
    /**
     * @var ELogs
     */
    protected $logFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param ELogs $logFactory
     */
    public function __construct(
        ELogs $logFactory,
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->logFactory = $logFactory;
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $logId = $this->getRequest()->getParam('id');
            $this->logFactory->create()->load($logId)->delete();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. %1', $e->getMessage())
            );
            $this->_redirect('*/smtp/log');

            return;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of 1 record have been deleted.')
        );

        return $resultRedirect->setPath('*/smtp/log');
    }
}
