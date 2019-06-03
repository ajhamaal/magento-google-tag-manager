<?php

declare(strict_types=1);

namespace DK\GoogleTagManager\Model\DataLayer\Generator;

use DK\GoogleTagManager\Model\DataLayer\Dto;
use DK\GoogleTagManager\Model\DataLayer\Generator;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

class CheckoutStep
{
    private const EVENT_CHECKOUT = 'checkout';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Generator\Product
     */
    private $productGenerator;

    public function __construct(
        Session $session,
        StoreManagerInterface $storeManager,
        Generator\Product $productGenerator
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->productGenerator = $productGenerator;
    }

    public function stepCheckout(int $step, string $option): Dto\Ecommerce
    {
        $products = [];
        foreach ($this->session->getQuote()->getAllVisibleItems() as $item) {
            $products[] = $this->productGenerator->generate($item->getProduct(), $item);
        }

        $actionField = new Dto\Cart\ActionField();
        $actionField->step = $step;
        $actionField->option = $option;

        $cart = new Dto\Cart\Cart();
        $cart->actionField = $actionField;
        $cart->products = $products;

        $checkout = new Dto\Cart\Checkout();
        $checkout->checkout = $cart;
        $checkout->currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        $ecommerce = new Dto\Ecommerce();
        $ecommerce->event = self::EVENT_CHECKOUT;
        $ecommerce->ecommerce = $checkout;

        return $ecommerce;
    }
}