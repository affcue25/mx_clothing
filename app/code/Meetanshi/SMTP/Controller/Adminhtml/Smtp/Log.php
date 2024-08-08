<?php
namespace Meetanshi\SMTP\Controller\Adminhtml\Smtp;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Log extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Meetanshi_SMTP::log';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Meetanshi_SMTP::log');
        $resultPage->getConfig()->getTitle()->prepend(__('Emails Log'));

        $resultPage->addBreadcrumb(__('Meetanshi'), __('Meetanshi'));
        $resultPage->addBreadcrumb(__('SMTP'), __('Email Logs'));

        return $resultPage;
    }
}
