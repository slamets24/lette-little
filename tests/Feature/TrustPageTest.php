<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrustPageTest extends TestCase
{
    public function test_size_guide_page_renders(): void
    {
        $this->get(route('size-guide'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('SizeGuide'));
    }

    public function test_care_guide_page_renders(): void
    {
        $this->get(route('care-guide'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('CareGuide'));
    }

    public function test_shipping_returns_page_renders(): void
    {
        $this->get(route('shipping-returns'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ShippingReturns'));
    }

    public function test_faq_page_renders(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Faq'));
    }

    public function test_privacy_page_renders(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Privacy'));
    }

    public function test_terms_page_renders(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Terms'));
    }
}
