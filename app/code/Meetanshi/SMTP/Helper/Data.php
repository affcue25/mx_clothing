<?php

namespace Meetanshi\SMTP\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Meetanshi\SMTP\Helper\AbstractData;

/**
 * Class Data
 * @package Meetanshi\SMTP\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'smtp';
    const CONFIG_GROUP_SMTP = 'configuration_option';
    const DEVELOP_GROUP_SMTP = 'developer';

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getSmtpConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getModuleConfig(self::CONFIG_GROUP_SMTP . $code, $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getDeveloperConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getModuleConfig(self::DEVELOP_GROUP_SMTP . $code, $storeId);
    }

    /**
     * @param null $storeId
     * @param bool $decrypt
     * @return array|mixed|string
     */
    public function getPassword($storeId = null, $decrypt = true)
    {
        if ($storeId || $storeId = $this->_request->getParam('store')) {
            $password = $this->getSmtpConfig('password', $storeId);
        } elseif ($websiteCode = $this->_request->getParam('website')) {
            $passwordPath = self::CONFIG_MODULE_PATH . '/' . self::CONFIG_GROUP_SMTP . '/password';
            $password = $this->getConfigValue($passwordPath, $websiteCode, ScopeInterface::SCOPE_WEBSITE);
        } else {
            $password = $this->getSmtpConfig('password');
        }

        if ($decrypt) {
            $encryptor = $this->getObject(EncryptorInterface::class);

            return $encryptor->decrypt($password);
        }

        return $password;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getScopeId()
    {
        $scope = $this->_request->getParam(ScopeInterface::SCOPE_STORE) ?: $this->storeManager->getStore()->getId();

        if ($website = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $scope = $this->storeManager->getWebsite($website)->getDefaultStore()->getId();
        }

        return $scope;
    }
}
