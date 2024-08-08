<?php
namespace Meetanshi\SMTP\Controller\Adminhtml\Smtp;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\SMTP\Model\LogsFactory;

/**
 * Class Email
 * @package Meetanshi\SMTP\Controller\Adminhtml\Smtp
 */
class Email extends Action
{
    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LogsFactory
     */
    protected $logFactory;

    /**
     * Email constructor.
     * @param Context $context
     * @param LogsFactory $logFactory
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        LogsFactory $logFactory,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->logFactory = $logFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $logId = $this->getRequest()->getParam('id');
        if (!$logId) {
            return $this->_redirect('*/*/log');
        }

        $email = $this->logFactory->create()->load($logId);
        if ($email->resendEmail()) {
            $this->messageManager->addSuccessMessage(__('Email re-sent successfully!'));
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t process your request right now.'));
        }
        $this->_redirect('*/smtp/log');
    }
}
