<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('email', 'marko.traders@example.com')->firstOrFail();

        $customers = [
            [
                'business_name' => 'Al-Hamd Electronics',
                'ntn_cnic' => '3520212345671',
                'province' => 'Punjab',
                'buyer_registration_type' => Customer::REGISTRATION_TYPE_REGISTERED,
                'strn' => '3277876100013',
                'contact_person' => 'Muhammad Imran',
                'email' => 'alhamd@example.com',
                'contact_number' => '03001234567',
                'address' => 'Hall Road, Lahore',
            ],
            [
                'business_name' => 'Pak Supplies Co.',
                'ntn_cnic' => '3740512345673',
                'province' => 'Sindh',
                'buyer_registration_type' => Customer::REGISTRATION_TYPE_REGISTERED,
                'strn' => '1234567890123',
                'contact_person' => 'Ayesha Khan',
                'email' => 'pak.supplies@example.com',
                'contact_number' => '03111234567',
                'address' => 'Shahrah-e-Faisal, Karachi',
            ],
            [
                'business_name' => 'City Mart',
                'ntn_cnic' => '6110112345675',
                'province' => 'Islamabad Capital Territory',
                'buyer_registration_type' => Customer::REGISTRATION_TYPE_UNREGISTERED,
                'strn' => null,
                'contact_person' => 'Bilal Ahmed',
                'email' => 'city.mart@example.com',
                'contact_number' => '03221234567',
                'address' => 'Blue Area, Islamabad',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['company_id' => $company->id, 'email' => $customer['email']],
                [...$customer, 'company_id' => $company->id, 'status' => Customer::STATUS_ACTIVE],
            );
        }
    }
}
