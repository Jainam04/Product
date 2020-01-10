<?php
namespace GetCP\Rest\Model;
use GetCP\Rest\Api\AddOrderInterface;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Service\OrderService;
use Magento\Store\Model\StoreManagerInterface;
 
class AddOrder implements AddOrderInterface
{
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CartManagementInterface $cartManagementInterface,
        CartRepositoryInterface $cartRepositoryInterface,
        OrderService $orderService,
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository        = $productRepository;
        $this->productFactory           = $productFactory;
        $this->customerRepository       = $customerRepository;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->cartManagementInterface  = $cartManagementInterface;
        $this->cartRepositoryInterface  = $cartRepositoryInterface;
        $this->orderService             = $orderService;
        $this->storeManager             = $storeManager;
    }
 
    public function namea($name)
    {
        $lastname = $_GET['lastname'];
        $street = $_GET['street']; 
        $city = $_GET['city'];
        $telephone = $_GET['telephone'];
        $product_id = $_GET['product_id'];
        $orderData = [
            'currency_id'  => 'GBP',
            'email'        => $name.'123@gmail.com',
            'guest_order'  => true,
            'shipping_address'      => [
                'firstname'            => $name,
                'lastname'             => $lastname,
                'street'               => $street,
                'city'                 => $city,
                'country_id'           => 'GB',
                'region'               => 'xxx',
                'postcode'             => 'LN1 1AA',
                'telephone'            => $telephone,
                'save_in_address_book' => 1
            ],
            'items'=> [
                ['product_id' => $product_id,'qty' => 1]
            ]
        ];
 
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
 
        // Initialise Cart
        $cartId = $this->cartManagementInterface->createEmptyCart();
        $cart = $this->cartRepositoryInterface->get($cartId);
        $cart->setStore($store);
        $cart->setCurrency();
 
        // Check if guest order
        if ($orderData['guest_order']) {
            $cart->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
            $cart->getBillingAddress()->setEmail($orderData['email']);
        } else {
            $customer = $this->customerInterfaceFactory->create();
            $customer->setWebsiteId($websiteId);
 
            // Check if the customer's email address exists
            try {
                $customerEntity = $this->customerRepository->get($orderData['email'], $websiteId);
            } catch (NoSuchEntityException $e) {
                // If it doesn't, create the customer
                $customer
                    ->setFirstname($orderData['shipping_address']['firstname'])
                    ->setLastname($orderData['shipping_address']['lastname'])
                    ->setEmail($orderData['email']);
                $customerEntity = $this->customerRepository->save($customer);
            }
 
 
            $customerId = $this->customerRepository->getById($customerEntity->getId());
            $cart->assignCustomer($customerId);
        }
 
        // Add items to cart
        foreach ($orderData['items'] as $item) {
            $product = $this->productRepository->getById($item['product_id']);
            $cart->addProduct(
                $product,
                $item['qty']
            );
        }
 
        // Set billing and shipping addresses
        $cart->getBillingAddress()->addData($orderData['shipping_address']);
        $cart->getShippingAddress()->addData($orderData['shipping_address']);
 
        $shippingAddress = $cart->getShippingAddress();
 
        // Set shipping method
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate');
 
        // Set payment method
        $cart->setPaymentMethod('checkmo');
        $cart->getPayment()->importData(['method' => 'checkmo']);
 
        $cart->collectTotals();
        $cart->save();
 
        // Place the order
        $cart = $this->cartRepositoryInterface->get($cart->getId());
        $orderId = $this->cartManagementInterface->placeOrder($cart->getId());
        return "Order placed successfully. Your Order No is ".$orderId;
    }
}