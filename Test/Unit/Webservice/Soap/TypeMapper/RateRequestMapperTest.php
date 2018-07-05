<?php
/**
 * See LICENSE.md for license details.
 */

namespace Dhl\Express\Webservice\Soap\TypeMapper;

use Dhl\Express\Model\Request\Insurance;
use Dhl\Express\Webservice\Soap\Type\Common\SpecialServices\ServiceType;
use PHPUnit\Framework\TestCase;
use Dhl\Express\Model\RateRequest;
use Dhl\Express\Model\Request\Package;
use Dhl\Express\Model\Request\RecipientAddress;
use Dhl\Express\Model\Request\ShipmentDetails;
use Dhl\Express\Model\Request\ShipperAddress;
use Dhl\Express\Webservice\Soap\Type\Common\SpecialServices\Service;
use Dhl\Express\Webservice\Soap\Type\RateRequest as soapRateRequest;

/**
 * @package  Dhl\Express\Test\Unit
 * @author   Ronny Gertler <ronny.gertler@netresearch.de>
 * @license  https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.netresearch.de/
 */
class RateRequestMapperTest extends TestCase
{
    /**
     * @test
     */
    public function getSoapRateRequestFromRateRequest()
    {
        // Set up a RateRequest

        $shipperAddress = new ShipperAddress(
            $countryCode = 'DE',
            $postalCode = '12345',
            $city = 'Berlin'
        );

        $shipperAccountNumber = 'XXXXXXXXX';

        $recipientAddress = new RecipientAddress(
            $countryCode = 'DE',
            $postalCode = '12345',
            $city = 'Berlin',
            $streetLines = ['Sample street 5a', 'Sample street 5b']
        );

        $shipmentDetails = new ShipmentDetails(
            $unscheduledPickup = true,
            $termsOfTrade = 'CFR',
            $contentType = ShipmentDetails::CONTENT_TYPE_DOCUMENTS,
            $readyAtTimestamp = 238948923
        );

        $package = new Package(
            $sequenceNumber = 1,
            $weight = 1.123,
            $weightUOM = 'KG',
            $length = 1.123,
            $width = 1.123,
            $height = 1.123,
            $dimensionUOM = 'CM'
        );

        $packages = [$package, $package];

        $insurance = new Insurance(99.99, 'EUR');

        $rateRequest = new RateRequest(
            $shipperAddress,
            $shipperAccountNumber,
            $recipientAddress,
            $shipmentDetails,
            $packages,
            $insurance
        );

        // Map Rate Request to SOAP Rate Request

        $rateRequestMapper = new RateRequestMapper();
        $soapRateRequest = $rateRequestMapper->map($rateRequest);

        // Assertions

        $this->assertInstanceOf(soapRateRequest::class, $soapRateRequest);

        $this->assertEquals(
            $shipperAddress->getCity(),
            $soapRateRequest->getRequestedShipment()->getShip()->getShipper()->getCity()
        );
        $this->assertEquals(
            $shipperAddress->getPostalCode(),
            $soapRateRequest->getRequestedShipment()->getShip()->getShipper()->getPostalCode()
        );
        $this->assertEquals(
            $shipperAddress->getCountryCode(),
            $soapRateRequest->getRequestedShipment()->getShip()->getShipper()->getCountryCode()
        );

        $this->assertEquals($shipperAccountNumber, $soapRateRequest->getRequestedShipment()->getAccount());

        $this->assertEquals(
            $recipientAddress->getCountryCode(),
            $soapRateRequest->getRequestedShipment()->getShip()->getRecipient()->getCountryCode()
        );
        $this->assertEquals(
            $recipientAddress->getPostalCode(),
            $soapRateRequest->getRequestedShipment()->getShip()->getRecipient()->getPostalCode()
        );

        if (count($recipientAddress->getStreetLines())) {
            $this->assertEquals(
                $recipientAddress->getStreetLines()[0],
                $soapRateRequest->getRequestedShipment()->getShip()->getRecipient()->getStreetLines()
            );
        }

        if (count($recipientAddress->getStreetLines()) > 1) {
            $this->assertEquals(
                $recipientAddress->getStreetLines()[1],
                $soapRateRequest->getRequestedShipment()->getShip()->getRecipient()->getStreetLines2()
            );
        }

        if (count($recipientAddress->getStreetLines()) > 2) {
            $this->assertEquals(
                $recipientAddress->getStreetLines()[2],
                $soapRateRequest->getRequestedShipment()->getShip()->getRecipient()->getStreetLines3()
            );
        }

        $this->assertEquals('REQUEST_COURIER', $soapRateRequest->getRequestedShipment()->getDropOffType());

        $this->assertEquals($termsOfTrade, $soapRateRequest->getRequestedShipment()->getPaymentInfo());
        $this->assertEquals($contentType, $soapRateRequest->getRequestedShipment()->getContent()->__toString());
        $this->assertEquals(
            'SI',
            $soapRateRequest->getRequestedShipment()->getUnitOfMeasurement()->__toString()
        );

        /**
         * @Todo Test $weightUOM
         */

        /**
         * @var soapRateRequest\Packages\RequestedPackages $soapPackage
         */
        $soapPackage = $soapRateRequest->getRequestedShipment()->getPackages()->getRequestedPackages()[0];
        $this->assertSameSize(
            $packages,
            $soapRateRequest->getRequestedShipment()->getPackages()->getRequestedPackages()
        );
        $this->assertEquals($package->getSequenceNumber(), $soapPackage->getNumber());
        $this->assertEquals(
            $shipmentDetails->getReadyAtTimestamp(),
            \DateTime::createFromFormat('Y-m-d\TH:i:s \G\M\TP', $soapRateRequest->getRequestedShipment()->getShipTimestamp())
                ->getTimestamp()
        );

        $this->assertEquals($package->getHeight(), $soapPackage->getDimensions()->getHeight()->getValue());
        $this->assertEquals($package->getWidth(), $soapPackage->getDimensions()->getWidth()->getValue());
        $this->assertEquals($package->getLength(), $soapPackage->getDimensions()->getLength()->getValue());
        $this->assertEquals($package->getWeight(), $soapPackage->getWeight()->getValue());

        /**
         * @var Service[] $soapSpecialServices
         */
        $soapSpecialServices = $soapRateRequest->getRequestedShipment()->getSpecialServices()->getService();
        $soapInsurance = null;
        /**
         * @var Service $soapService
         */
        foreach ($soapSpecialServices as $soapService) {
            if ($soapService->getServiceType()->__toString() === ServiceType::TYPE_INSURANCE) {
                $soapInsurance = $soapService;
            }
        }

        $this->assertInstanceOf(Service::class, $soapInsurance);
        $this->assertSame($insurance->getCurrencyCode(), $soapInsurance->getCurrencyCode()->__toString());
        $this->assertSame($insurance->getValue(), $soapInsurance->getServiceValue()->getValue());
    }
}