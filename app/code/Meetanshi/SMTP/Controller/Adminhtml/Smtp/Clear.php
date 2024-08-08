<?php
namespace Meetanshi\SMTP\Controller\Adminhtml\Smtp;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Meetanshi\SMTP\Model\ResourceModel\Logs\Grid\Collection;
use Meetanshi\SMTP\Model\ResourceModel\Logs\Grid\CollectionFactory;

/**
 * Class Clear
 * @package Meetanshi\SMTP\Controller\Adminhtml\Smtp
 */
class Clear extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Meetanshi_SMTP::smtp';

    /**
     * @var Collection
     */
    protected $collectionLog;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionLog
     * @param Context $context
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionLog
    ) {
        $this->collectionLog = $collectionLog;

        parent::__construct($context);
    }

    /**
     * Clear Emails Log
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var Collection $collection */
        $collection = $this->collectionLog->create();

        try {
            $collection->getConnection()->truncateTable($collection->getMainTable());
            $this->messageManager->addSuccess(__('Success'));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong.'));
        }

        return $resultRedirect->setPath('*/*/log');
    }
}
