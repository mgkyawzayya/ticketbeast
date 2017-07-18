<?php

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderTest extends TestCase
{
	use DatabaseMigrations;
    
    /** @test */
    public function creating_an_order_from_tickets_email_and_amount()
    {
        $concert = factory(Concert::class)->create()->addTickets(5);
        
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order = Order::forTickets($concert->findTickets(3), 'holly@thedog.com', 3600);

        $this->assertEquals($order->email, 'holly@thedog.com');
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);

        $this->assertEquals(2, $concert->fresh()->ticketsRemaining());
    }

    /** @test */
    public function retrieving_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create(['confirmation_number' => 'CONFIRMATION1234']);

        $foundOrder = Order::findByConfirmationNumber('CONFIRMATION1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    public function retrieving_a_non_existant_order_by_confirmation_order_throws_an_exception()
    {
        try {
            Order::findByConfirmationNumber('NONEXISTANTCONFIRMATION');
        } catch (ModelNotFoundException $e) {
            return;
        }
        $this->fail('No matching order was found for the matching confirmation number but, a ModelNotFoundException was not thrown.');
    }

    /** @test */
    public function converting_to_an_array()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(5);

        $order = $concert->orderTickets('duchess@thedog.com', 5);
        $result = $order->toArray();

        $this->assertEquals([
            'email'             => 'duchess@thedog.com',
            'ticket_quantity'   => 5,
            'amount'            => 6000
        ], $result);
    }
}
