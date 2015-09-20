<?php

/**
 * Class Eeree_OneProductAtATime_Model_Observer
 */
class Eeree_OneProductAtATime_Model_Observer extends Varien_Event_Observer
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

    /**
     * @var array
     */
    protected $quoteItems;

    /**
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     */
    public function catalogProductTypePrepareFullOptions(Varien_Event_Observer $observer) {
        $this->_checkTotalItemQuantityInCart();
        $this->_checkQuantityOfProductsInCart();
        $this->_checkItemsLimitPerProduct($observer);
    }

    /**
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     */
    protected function _checkItemsLimitPerProduct(Varien_Event_Observer $observer) {
        $maxItemsPerProduct = Mage::getStoreConfig('checkout/options/max_per_product_cart_qty');
        /** @noinspection PhpUndefinedMethodInspection */
        $productId = $observer->getProduct()->getEntityId();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($this->_getQuoteItems() as $quoteItem) {
            if ($quoteItem->getProductId() === $productId && $quoteItem->getQty() > $maxItemsPerProduct) {
                Mage::throwException(Mage::helper('eeree_oneproductatatime')->__('You can not add any more products of this type to your cart. Max of %d has been reached', $maxItemsPerProduct));
            }
        }
    }

    /**
     * @throws Mage_Core_Exception
     */
    protected function _checkQuantityOfProductsInCart() {
        $maxProductsPerCart = Mage::getStoreConfig('checkout/options/max_total_products_cart_qty');
        if ($maxProductsPerCart && count($this->_getQuoteItems()) > $maxProductsPerCart) {
            Mage::throwException(Mage::helper('eeree_oneproductatatime')->__('You can not add any more products to your cart. Maximum quantity of unique products equals to %d', $maxProductsPerCart));
        }

    }

    /**
     * @throws Mage_Core_Exception
     */
    protected function _checkTotalItemQuantityInCart() {
        $maxItemsPerCart = Mage::getStoreConfig('checkout/options/max_total_cart_qty');
        if ($maxItemsPerCart && $maxItemsPerCart > $this->_getQuote()->getItemsCount()) {
            Mage::throwException(Mage::helper('eeree_oneproductatatime')->__('You can not add any more items to you cart. Max of %d has been reached', $maxItemsPerCart));
        }
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote() {
        if ($this->quote === null) {
            $this->quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        return $this->quote;
    }

    /**
     * @return array
     */
    protected function _getQuoteItems() {
        if ($this->quoteItems === null) {
            $this->quoteItems = $this->_getQuote()->getAllItems();
        }

        return $this->quoteItems;
    }
}
