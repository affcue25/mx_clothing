<?php
namespace Meetanshi\SMTP\Controller\Adminhtml\Smtp;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Meetanshi\SMTP\Model\ResourceModel\Logs\CollectionFactory;

/**
 * Class MassResend
 * @package Magetop\Smtp\Controller\Adminhtml\Smtp
 */
class MassResend extends Action
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
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * MassResend constructor.
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
     * @return $this|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->emailLog->create());
        $resend = 0;

        foreach ($collection->getItems() as $item) {
            if ($item->resendEmail()) {
                $resend++;
            } else {
                $this->messageManager->addErrorMessage(
                    __('We can\'t process your request for email log #%1', $item->getId())
                );
            }
        }

        if ($resend) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 mail(s) have been sent.', $resend)
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('mt_smtp/smtp/log');
    }
}
