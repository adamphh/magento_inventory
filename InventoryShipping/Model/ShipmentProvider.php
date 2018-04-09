<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Shipping\Model\ShipmentProviderInterface;
use Magento\Framework\App\RequestInterface;

class ShipmentProvider implements ShipmentProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function getShipment()
    {
        $sourceCode = $this->request->getParam('sourceCode');
        $items = $this->request->getParam('items', []);

        $shipmentItems = [];
        foreach ($items as $item) {
            if (empty($item['sources'])) {
                continue;
            }
            $orderItemId = $item['orderItemId'];
            foreach ($item['sources'] as $source) {
                if ($source['sourceCode'] == $sourceCode) {
                    $qty = ($shipmentItems[$orderItemId] ?? 0) + (float)$source['qtyToDeduct'];
                    $shipmentItems['items'][$orderItemId] = $qty;
                }
            }
        }

        return count($shipmentItems) > 0 ? $shipmentItems : null;
    }
}