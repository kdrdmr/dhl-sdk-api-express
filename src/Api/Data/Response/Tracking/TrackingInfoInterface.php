<?php
/**
 * See LICENSE.md for license details.
 */
namespace Dhl\Express\Api\Data\Response\Tracking;

use Dhl\Express\Api\Data\Response\Tracking\TrackingInfo\PieceInterface;
use Dhl\Express\Api\Data\Response\Tracking\TrackingInfo\ShipmentDetailsInterface;
use Dhl\Express\Api\Data\Response\Tracking\TrackingInfo\ShipmentEventInterface;
use Dhl\Express\Webservice\Soap\Type\Tracking\ConditionCollection;

/**
 * TrackingInfo class.
 *
 * DTO that carries relevant data for processing the tracking result.
 *
 * @api
 * @author   Ronny Gertler <ronny.gertler@netresearch.de>
 * @link     https://www.netresearch.de/
 */
interface TrackingInfoInterface
{
    /**
     * Returns the AWB number.
     *
     * @return string
     */
    public function getAwbNumber();

    /**
     * Returns the status.
     *
     * @return string
     */
    public function getAwbStatus();

    /**
     * Returns the shipment Details.
     *
     * @return ShipmentDetailsInterface
     */
    public function getShipmentDetails();

    /**
     * Returns the shipment events.
     *
     * @return ShipmentEventInterface[]
     */
    public function getShipmentEvents();

    /**
     * Returns the pieces
     *
     * @return PieceInterface[]
     */
    public function getPieces();

    /**
     * @return ConditionCollection
     */
    public function getAwbConditions();

    /**
     * @param ConditionCollection $awbConditions
     */
    public function setAwbConditions($awbConditions);

}
