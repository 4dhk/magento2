<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Pricing\Render;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\AbstractAdjustment;
use Magento\Framework\View\Element\Template;
use Magento\Weee\Model\Tax;

/**
 * Weee Price Adjustment that handles Weee specific amount and its display
 */
class Adjustment extends AbstractAdjustment
{
    /**
     * Weee helper
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var float
     */
    protected $finalAmount;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Weee\Helper\Data $weeeHelper,
        array $data = []
    ) {
        $this->weeeHelper = $weeeHelper;
        parent::__construct($context, $priceCurrency, $data);
    }

    /**
     * @return null
     */
    protected function apply()
    {
        if ($this->typeOfDisplay([Tax::DISPLAY_EXCL, Tax::DISPLAY_EXCL_DESCR_INCL])) {
            $this->finalAmount = $this->amountRender->getDisplayValue();

            if ($this->typeOfDisplay([Tax::DISPLAY_EXCL])) {
                $this->amountRender->setDisplayValue(
                    $this->amountRender->getDisplayValue() -
                    $this->amountRender->getAmount()->getAdjustmentAmount($this->getAdjustmentCode())
                );
            } else {
                $weeeTaxAmount = 0;
                $attributes =
                    $this->weeeHelper->getProductWeeeAttributes($this->getSaleableItem(), null, null, null, true);
                if ($attributes != null) {
                    foreach ($attributes as $attribute) {
                        $weeeTaxAmount += $attribute->getData('tax_amount');
                    }
                }
                $this->amountRender->setDisplayValue(
                    $this->amountRender->getDisplayValue() -
                    $this->amountRender->getAmount()->getAdjustmentAmount($this->getAdjustmentCode()) -
                    $weeeTaxAmount
                );
            }
        }
        return $this->toHtml();
    }

    /**
     * @return float
     */
    public function getRawFinalAmount()
    {
        return $this->finalAmount;
    }

    /**
     * Obtain adjustment code
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        return \Magento\Weee\Pricing\Adjustment::ADJUSTMENT_CODE;
    }

    /**
     * @return float
     */
    public function getFinalAmount()
    {
        return $this->formatCurrency($this->finalAmount);
    }

    /**
     * Get weee amount
     *
     * @return float
     */
    protected function getWeeeTaxAmount()
    {
        $product = $this->getSaleableItem();
        return $this->weeeHelper->getAmount($product);
    }

    /**
     * Define if adjustment should be shown with including tax, description
     *
     * @return bool
     */
    public function showInclDescr()
    {
        return $this->isWeeeShown() && $this->getWeeeTaxAmount() && $this->typeOfDisplay(Tax::DISPLAY_INCL_DESCR);
    }

    /**
     * Define if adjustment should be shown with including tax, excluding tax, description
     *
     * @return bool
     */
    public function showExclDescrIncl()
    {
        return $this->isWeeeShown() && $this->getWeeeTaxAmount() && $this->typeOfDisplay(Tax::DISPLAY_EXCL_DESCR_INCL);
    }

    /**
     * Obtain Weee tax attributes
     *
     * @return array|\Magento\Framework\DataObject[]
     */
    public function getWeeeTaxAttributes()
    {
        return $this->isWeeeShown() ? $this->getWeeeAttributesForDisplay() : [];
    }

    /**
     * Render Weee tax attributes name
     *
     * @param \Magento\Framework\DataObject $attribute
     * @return string
     */
    public function renderWeeeTaxAttributeName(\Magento\Framework\DataObject $attribute)
    {
        return $attribute->getData('name');
    }

    /**
     * Render Weee tax attributes value
     *
     * @param \Magento\Framework\DataObject $attribute
     * @return string
     */
    public function renderWeeeTaxAttribute(\Magento\Framework\DataObject $attribute)
    {
        return $this->convertAndFormatCurrency($attribute->getData('amount'));
    }

    /**
     * Render Weee tax attributes value
     *
     * @param \Magento\Framework\DataObject $attribute
     * @return string
     */
    public function renderWeeeTaxAttributeWithTax(\Magento\Framework\DataObject $attribute)
    {
        return $this->convertAndFormatCurrency($attribute->getData('amount') + $attribute->getData('tax_amount'));
    }

    /**
     * Returns display type for price accordingly to current zone
     *
     * @param int|int[]|null $compareTo
     * @param \Magento\Store\Model\Store|null $store
     * @return bool|int
     */
    protected function typeOfDisplay($compareTo = null, $store = null)
    {
        return $this->weeeHelper->typeOfDisplay($compareTo, $this->getZone(), $store);
    }

    /**
     * Get Weee attributes for display
     *
     * @return \Magento\Framework\DataObject[]
     */
    protected function getWeeeAttributesForDisplay()
    {
        $product = $this->getSaleableItem();
        return $this->weeeHelper->getProductWeeeAttributesForDisplay($product);
    }

    /**
     * Returns whether Weee should be displayed
     *
     * @return bool
     */
    protected function isWeeeShown()
    {
        $isWeeeShown = $this->typeOfDisplay([Tax::DISPLAY_INCL_DESCR, Tax::DISPLAY_EXCL_DESCR_INCL]);
        return $isWeeeShown;
    }

    /**
     * Returns whether tax should be shown (according to Price Display Settings)
     *
     * @return bool
     */
    public function showPriceWithTax()
    {
        $showPriceWithTax = $this->weeeHelper->showPriceWithTax();
        return $showPriceWithTax;
    }
}
