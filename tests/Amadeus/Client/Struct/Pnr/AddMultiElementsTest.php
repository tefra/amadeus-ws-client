<?php
/**
 * amadeus-ws-client
 *
 * Copyright 2015 Amadeus Benelux NV
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package Amadeus
 * @license https://opensource.org/licenses/Apache-2.0 Apache 2.0
 */

namespace Test\Amadeus\Client\Struct\Pnr;

use Amadeus\Client\RequestOptions\Pnr\Element\Contact;
use Amadeus\Client\RequestOptions\Pnr\Element\FormOfPayment;
use Amadeus\Client\RequestOptions\Pnr\Element\ServiceRequest;
use Amadeus\Client\RequestOptions\Pnr\Element\Ticketing;
use Amadeus\Client\RequestOptions\Pnr\Reference;
use Amadeus\Client\RequestOptions\Pnr\Segment\Miscellaneous;
use Amadeus\Client\RequestOptions\Pnr\Traveller;
use Amadeus\Client\RequestOptions\PnrCreatePnrOptions;
use Amadeus\Client\RequestOptions\Queue;
use Amadeus\Client\Struct\Pnr\AddMultiElements;
use Amadeus\Client\Struct\Pnr\AddMultiElements\PnrActions;
use Test\Amadeus\BaseTestCase;

/**
 * AddMultiElementsTest
 *
 * @package Amadeus\Client\Struct\Pnr
 */
class AddMultiElementsTest extends BaseTestCase
{

    public function testCanCreateMessageToCreateBasicPnr()
    {
        $createPnrOptions = new PnrCreatePnrOptions();
        $createPnrOptions->receivedFrom = "unittest";
        $createPnrOptions->travellers[] = new Traveller([
            'number' => 1,
            'lastName' => 'Bowie',
            'firstName' => 'David'
        ]);
        $createPnrOptions->actionCode = PnrActions::ACTIONOPTION_END_TRANSACT_W_RETRIEVE;
        $createPnrOptions->tripSegments[] = new Miscellaneous([
            'date' => \DateTime::createFromFormat('Y-m-d', "2016-10-02", new \DateTimeZone('UTC')),
            'cityCode' => 'BRU',
            'freeText' => 'GENERIC TRAVEL REQUEST',
            'company' => '1A'
        ]);
        $createPnrOptions->elements[] = new Ticketing([
            'ticketMode' => Ticketing::TICKETMODE_TIMELIMIT,
            'date' => \DateTime::createFromFormat(\DateTime::ISO8601, "2016-01-27T00:00:00+0000", new \DateTimeZone('UTC')),
            'ticketQueue' => new Queue(['queue' => 50, 'category' => 0])
        ]);
        $createPnrOptions->elements[] = new Contact([
            'type' => Contact::TYPE_PHONE_GENERAL,
            'value' => '+3223456789'
        ]);

        $requestStruct = new AddMultiElements($createPnrOptions);

        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\DataElementsMaster', $requestStruct->dataElementsMaster);

        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\PnrActions', $requestStruct->pnrActions);
        $this->assertEquals(PnrActions::ACTIONOPTION_END_TRANSACT_W_RETRIEVE, $requestStruct->pnrActions->optionCode);

        $this->assertInternalType('array', $requestStruct->travellerInfo);
        $this->assertEquals(1, count($requestStruct->travellerInfo));
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\TravellerInfo', $requestStruct->travellerInfo[0]);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\ElementManagementPassenger', $requestStruct->travellerInfo[0]->elementManagementPassenger);
        $this->assertEquals(AddMultiElements\ElementManagementPassenger::SEG_NAME, $requestStruct->travellerInfo[0]->elementManagementPassenger->segmentName);
        $this->assertNull($requestStruct->travellerInfo[0]->elementManagementPassenger->reference);
        $this->assertInternalType('array', $requestStruct->travellerInfo[0]->passengerData);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\PassengerData', $requestStruct->travellerInfo[0]->passengerData[0]);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\TravellerInformation', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\Traveller', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->traveller);
        $this->assertEquals('Bowie', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->traveller->surname);
        $this->assertInternalType('array', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->passenger);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\Passenger', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->passenger[0]);
        $this->assertEquals('David', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->passenger[0]->firstName);
        $this->assertEquals(AddMultiElements\Passenger::PASST_ADULT, $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->passenger[0]->type);

        $this->assertInternalType('array', $requestStruct->originDestinationDetails);
        $this->assertEquals(1, count($requestStruct->originDestinationDetails));
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\OriginDestinationDetails', $requestStruct->originDestinationDetails[0]);
        $this->assertNull($requestStruct->originDestinationDetails[0]->originDestination);
        $this->assertInternalType('array', $requestStruct->originDestinationDetails[0]->itineraryInfo);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\ItineraryInfo', $requestStruct->originDestinationDetails[0]->itineraryInfo[0]);
        $this->assertEquals(AddMultiElements\ElementManagementItinerary::SEGMENT_MISCELLANEOUS, $requestStruct->originDestinationDetails[0]->itineraryInfo[0]->elementManagementItinerary->segmentName);
        $this->assertEquals(AddMultiElements\Reference::QUAL_OTHER, $requestStruct->originDestinationDetails[0]->itineraryInfo[0]->elementManagementItinerary->reference->qualifier);
        $this->assertEquals('GENERIC TRAVEL REQUEST', $requestStruct->originDestinationDetails[0]->itineraryInfo[0]->airAuxItinerary->freetextItinerary->longFreetext);
        $this->assertEquals(AddMultiElements\RelatedProduct::STATUS_CONFIRMED, $requestStruct->originDestinationDetails[0]->itineraryInfo[0]->airAuxItinerary->relatedProduct->status);


        $this->assertInternalType('array', $requestStruct->dataElementsMaster->dataElementsIndiv);
        $this->assertEquals(3, count($requestStruct->dataElementsMaster->dataElementsIndiv));
        $this->assertEquals('TK', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->elementManagementData->segmentName);
        $this->assertEquals(AddMultiElements\TicketElement::PASSTYPE_PAX, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->passengerType);
        $this->assertEquals('270116', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->date);
        $this->assertNull($requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->time);
        $this->assertEquals(50, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->queueNumber);
        $this->assertEquals(0, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->queueCategory);
        $this->assertEquals(AddMultiElements\Ticket::TICK_IND_TL, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->indicator);

        $this->assertEquals('AP', $requestStruct->dataElementsMaster->dataElementsIndiv[1]->elementManagementData->segmentName);
        $this->assertEquals(AddMultiElements\FreetextDetail::TYPE_PHONE_GENERAL, $requestStruct->dataElementsMaster->dataElementsIndiv[1]->freetextData->freetextDetail->type);
        $this->assertEquals('+3223456789', $requestStruct->dataElementsMaster->dataElementsIndiv[1]->freetextData->longFreetext);

        $this->assertEquals('RF', $requestStruct->dataElementsMaster->dataElementsIndiv[2]->elementManagementData->segmentName);
        $this->assertEquals('unittest', $requestStruct->dataElementsMaster->dataElementsIndiv[2]->freetextData->longFreetext);
    }

    public function testCanCreateMessageToCreateBasicPnrWithSrDocs()
    {
        $createPnrOptions = new PnrCreatePnrOptions();
        $createPnrOptions->receivedFrom = "unittest";
        $createPnrOptions->travellers[] = new Traveller([
            'number' => 1,
            'lastName' => 'Bowie',
            'firstName' => 'David'
        ]);
        $createPnrOptions->actionCode = PnrActions::ACTIONOPTION_END_TRANSACT_W_RETRIEVE;
        $createPnrOptions->tripSegments[] = new Miscellaneous([
            'date' => \DateTime::createFromFormat('Y-m-d', "2016-10-02", new \DateTimeZone('UTC')),
            'cityCode' => 'BRU',
            'freeText' => 'GENERIC TRAVEL REQUEST',
            'company' => '1A'
        ]);
        $createPnrOptions->elements[] = new Ticketing([
            'ticketMode' => Ticketing::TICKETMODE_TIMELIMIT,
            'date' => \DateTime::createFromFormat(\DateTime::ISO8601, "2016-01-27T00:00:00+0000", new \DateTimeZone('UTC')),
            'ticketQueue' => new Queue(['queue' => 50, 'category' => 0])
        ]);
        $createPnrOptions->elements[] = new Contact([
            'type' => Contact::TYPE_PHONE_GENERAL,
            'value' => '+3223456789'
        ]);
        $createPnrOptions->elements[] = new ServiceRequest([
            'type' => 'DOCS',
            'status' => ServiceRequest::STATUS_HOLD_CONFIRMED,
            'company' => '1A',
            'quantity' => 1,
            'freeText' => [
                '----08JAN47-M--BOWIE-DAVID'
            ],
            'references' => [
                new Reference([
                    'type' => Reference::TYPE_PASSENGER_TATOO,
                    'id' => 1
                ])
            ]
        ]);

        $requestStruct = new AddMultiElements($createPnrOptions);

        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\DataElementsMaster', $requestStruct->dataElementsMaster);

        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\PnrActions', $requestStruct->pnrActions);
        $this->assertEquals(PnrActions::ACTIONOPTION_END_TRANSACT_W_RETRIEVE, $requestStruct->pnrActions->optionCode);

        $this->assertInternalType('array', $requestStruct->travellerInfo);
        $this->assertEquals(1, count($requestStruct->travellerInfo));
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\TravellerInfo', $requestStruct->travellerInfo[0]);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\ElementManagementPassenger', $requestStruct->travellerInfo[0]->elementManagementPassenger);
        $this->assertEquals(AddMultiElements\ElementManagementPassenger::SEG_NAME, $requestStruct->travellerInfo[0]->elementManagementPassenger->segmentName);
        $this->assertNull($requestStruct->travellerInfo[0]->elementManagementPassenger->reference);
        $this->assertInternalType('array', $requestStruct->travellerInfo[0]->passengerData);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\PassengerData', $requestStruct->travellerInfo[0]->passengerData[0]);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\TravellerInformation', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\Traveller', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->traveller);
        $this->assertEquals('Bowie', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->traveller->surname);
        $this->assertInternalType('array', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->passenger);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\Passenger', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->passenger[0]);
        $this->assertEquals('David', $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->passenger[0]->firstName);
        $this->assertEquals(AddMultiElements\Passenger::PASST_ADULT, $requestStruct->travellerInfo[0]->passengerData[0]->travellerInformation->passenger[0]->type);

        $this->assertInternalType('array', $requestStruct->originDestinationDetails);
        $this->assertEquals(1, count($requestStruct->originDestinationDetails));
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\OriginDestinationDetails', $requestStruct->originDestinationDetails[0]);
        $this->assertNull($requestStruct->originDestinationDetails[0]->originDestination);
        $this->assertInternalType('array', $requestStruct->originDestinationDetails[0]->itineraryInfo);
        $this->assertInstanceOf('Amadeus\Client\Struct\Pnr\AddMultiElements\ItineraryInfo', $requestStruct->originDestinationDetails[0]->itineraryInfo[0]);
        $this->assertEquals(AddMultiElements\ElementManagementItinerary::SEGMENT_MISCELLANEOUS, $requestStruct->originDestinationDetails[0]->itineraryInfo[0]->elementManagementItinerary->segmentName);
        $this->assertEquals(AddMultiElements\Reference::QUAL_OTHER, $requestStruct->originDestinationDetails[0]->itineraryInfo[0]->elementManagementItinerary->reference->qualifier);
        $this->assertEquals('GENERIC TRAVEL REQUEST', $requestStruct->originDestinationDetails[0]->itineraryInfo[0]->airAuxItinerary->freetextItinerary->longFreetext);
        $this->assertEquals(AddMultiElements\RelatedProduct::STATUS_CONFIRMED, $requestStruct->originDestinationDetails[0]->itineraryInfo[0]->airAuxItinerary->relatedProduct->status);


        $this->assertInternalType('array', $requestStruct->dataElementsMaster->dataElementsIndiv);
        $this->assertEquals(4, count($requestStruct->dataElementsMaster->dataElementsIndiv));
        $this->assertEquals('TK', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->elementManagementData->segmentName);
        $this->assertEquals(AddMultiElements\TicketElement::PASSTYPE_PAX, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->passengerType);
        $this->assertEquals('270116', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->date);
        $this->assertNull($requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->time);
        $this->assertEquals(50, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->queueNumber);
        $this->assertEquals(0, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->queueCategory);
        $this->assertEquals(AddMultiElements\Ticket::TICK_IND_TL, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->ticketElement->ticket->indicator);

        $this->assertEquals('AP', $requestStruct->dataElementsMaster->dataElementsIndiv[1]->elementManagementData->segmentName);
        $this->assertEquals(AddMultiElements\FreetextDetail::TYPE_PHONE_GENERAL, $requestStruct->dataElementsMaster->dataElementsIndiv[1]->freetextData->freetextDetail->type);
        $this->assertEquals('+3223456789', $requestStruct->dataElementsMaster->dataElementsIndiv[1]->freetextData->longFreetext);


        $this->assertEquals('SSR', $requestStruct->dataElementsMaster->dataElementsIndiv[2]->elementManagementData->segmentName);
        $this->assertEquals(1, $requestStruct->dataElementsMaster->dataElementsIndiv[2]->serviceRequest->ssr->quantity);
        $this->assertEquals('HK', $requestStruct->dataElementsMaster->dataElementsIndiv[2]->serviceRequest->ssr->status);
        $this->assertEquals('1A', $requestStruct->dataElementsMaster->dataElementsIndiv[2]->serviceRequest->ssr->companyId);
        $this->assertEquals('DOCS', $requestStruct->dataElementsMaster->dataElementsIndiv[2]->serviceRequest->ssr->type);
        $this->assertEquals('----08JAN47-M--BOWIE-DAVID', $requestStruct->dataElementsMaster->dataElementsIndiv[2]->serviceRequest->ssr->freetext[0]);
        $this->assertEquals(AddMultiElements\Reference::QUAL_PASSTAT, $requestStruct->dataElementsMaster->dataElementsIndiv[2]->referenceForDataElement->reference[0]->qualifier);
        $this->assertEquals(1, $requestStruct->dataElementsMaster->dataElementsIndiv[2]->referenceForDataElement->reference[0]->number);


        $this->assertEquals('RF', $requestStruct->dataElementsMaster->dataElementsIndiv[3]->elementManagementData->segmentName);
        $this->assertEquals('unittest', $requestStruct->dataElementsMaster->dataElementsIndiv[3]->freetextData->longFreetext);
    }

    public function testMakePnrWithFormOfPaymentCash()
    {

        $createPnrOptions = new PnrCreatePnrOptions();
        $createPnrOptions->receivedFrom = "unittest";
        $createPnrOptions->travellers[] = new Traveller([
            'number' => 1,
            'lastName' => 'Bowie',
            'firstName' => 'David'
        ]);
        $createPnrOptions->actionCode = PnrActions::ACTIONOPTION_END_TRANSACT_W_RETRIEVE;
        $createPnrOptions->tripSegments[] = new Miscellaneous([
            'date' => \DateTime::createFromFormat('Y-m-d', "2016-10-02", new \DateTimeZone('UTC')),
            'cityCode' => 'BRU',
            'freeText' => 'GENERIC TRAVEL REQUEST',
            'company' => '1A'
        ]);
        $createPnrOptions->elements[] = new FormOfPayment([
            'type' => FormOfPayment::TYPE_CASH
        ]);

        $requestStruct = new AddMultiElements($createPnrOptions);

        $this->assertInternalType('array', $requestStruct->dataElementsMaster->dataElementsIndiv);
        $this->assertEquals(2, count($requestStruct->dataElementsMaster->dataElementsIndiv));

        $this->assertEquals('FP', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->elementManagementData->segmentName);
        $this->assertEquals(AddMultiElements\Fop::IDENT_CASH, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->formOfPayment->fop->identification);
    }

    public function testMakePnrWithFormOfPaymentNonRef()
    {

        $createPnrOptions = new PnrCreatePnrOptions();
        $createPnrOptions->receivedFrom = "unittest";
        $createPnrOptions->travellers[] = new Traveller([
            'number' => 1,
            'lastName' => 'Bowie',
            'firstName' => 'David'
        ]);
        $createPnrOptions->actionCode = PnrActions::ACTIONOPTION_END_TRANSACT_W_RETRIEVE;
        $createPnrOptions->tripSegments[] = new Miscellaneous([
            'date' => \DateTime::createFromFormat('Y-m-d', "2016-10-02", new \DateTimeZone('UTC')),
            'cityCode' => 'BRU',
            'freeText' => 'GENERIC TRAVEL REQUEST',
            'company' => '1A'
        ]);
        $createPnrOptions->elements[] = new FormOfPayment([
            'type' => FormOfPayment::TYPE_MISC,
            'freeText' => 'NONREF'
        ]);

        $requestStruct = new AddMultiElements($createPnrOptions);

        $this->assertInternalType('array', $requestStruct->dataElementsMaster->dataElementsIndiv);
        $this->assertEquals(2, count($requestStruct->dataElementsMaster->dataElementsIndiv));

        $this->assertEquals('FP', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->elementManagementData->segmentName);
        $this->assertEquals(AddMultiElements\Fop::IDENT_MISC, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->formOfPayment->fop->identification);
        $this->assertEquals(1, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->fopExtension[0]->fopSequenceNumber);
    }

    public function testMakePnrWithFormOfPaymentCreditCard()
    {
        $createPnrOptions = new PnrCreatePnrOptions();
        $createPnrOptions->receivedFrom = "unittest";
        $createPnrOptions->travellers[] = new Traveller([
            'number' => 1,
            'lastName' => 'Bowie',
            'firstName' => 'David'
        ]);
        $createPnrOptions->actionCode = PnrActions::ACTIONOPTION_END_TRANSACT_W_RETRIEVE;
        $createPnrOptions->tripSegments[] = new Miscellaneous([
            'date' => \DateTime::createFromFormat('Y-m-d', "2016-10-02", new \DateTimeZone('UTC')),
            'cityCode' => 'BRU',
            'freeText' => 'GENERIC TRAVEL REQUEST',
            'company' => '1A'
        ]);
        $createPnrOptions->elements[] = new FormOfPayment([
            'type' => FormOfPayment::TYPE_CREDITCARD,
            'creditCardType' => 'VI',
            'creditCardNumber' => '4444333322221111',
            'creditCardExpiry' => '1017',
            'creditCardCvcCode' => 123
        ]);

        $requestStruct = new AddMultiElements($createPnrOptions);

        $this->assertInternalType('array', $requestStruct->dataElementsMaster->dataElementsIndiv);
        $this->assertEquals(2, count($requestStruct->dataElementsMaster->dataElementsIndiv));

        $this->assertEquals('FP', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->elementManagementData->segmentName);
        $this->assertEquals(AddMultiElements\Fop::IDENT_CREDITCARD, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->formOfPayment->fop->identification);
        $this->assertEquals('4444333322221111', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->formOfPayment->fop->accountNumber);
        $this->assertEquals('VI', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->formOfPayment->fop->creditCardCode);
        $this->assertEquals('1017', $requestStruct->dataElementsMaster->dataElementsIndiv[0]->formOfPayment->fop->expiryDate);
        $this->assertEquals(1, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->fopExtension[0]->fopSequenceNumber);
        $this->assertEquals(123, $requestStruct->dataElementsMaster->dataElementsIndiv[0]->fopExtension[0]->newFopsDetails->cvData);
    }

    public function testMakePnrWithPassengerDateOfBirth()
    {
        $createPnrOptions = new PnrCreatePnrOptions();
        $createPnrOptions->receivedFrom = "unittest";
        $createPnrOptions->travellers[] = new Traveller([
            'number' => 1,
            'lastName' => 'Bowie',
            'firstName' => 'David',
            'dateOfBirth' => \DateTime::createFromFormat('Y-m-d', '1947-01-08')
        ]);
        $createPnrOptions->actionCode = PnrActions::ACTIONOPTION_END_TRANSACT_W_RETRIEVE;
        $createPnrOptions->tripSegments[] = new Miscellaneous([
            'date' => \DateTime::createFromFormat('Y-m-d', "2016-10-02", new \DateTimeZone('UTC')),
            'cityCode' => 'BRU',
            'freeText' => 'GENERIC TRAVEL REQUEST',
            'company' => '1A'
        ]);

        $requestStruct = new AddMultiElements($createPnrOptions);

        $this->assertEquals(1, count($requestStruct->travellerInfo));
        $this->assertEquals(1, count($requestStruct->travellerInfo[0]->passengerData));
        $this->assertEquals(706, $requestStruct->travellerInfo[0]->passengerData[0]->dateOfBirth->dateAndTimeDetails->qualifier);
        $this->assertEquals('08011947', $requestStruct->travellerInfo[0]->passengerData[0]->dateOfBirth->dateAndTimeDetails->date);
    }
}
