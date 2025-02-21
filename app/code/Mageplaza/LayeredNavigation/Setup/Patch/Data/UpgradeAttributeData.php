<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mageplaza\LayeredNavigation\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Backend\Attributes;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\Attributes as SourceAttributes;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class UpgradeAttributeData implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(Category::ENTITY, 'mp_ln_hide_attribute_ids', [
            'type' => 'text',
            'label' => 'Hide Filter Attributes on Layered Navigation',
            'input' => 'multiselect',
            'source' => SourceAttributes::class,
            'backend' => Attributes::class,
            'required' => false,
            'sort_order' => 100,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'Display Settings',
        ]);

        $setup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
