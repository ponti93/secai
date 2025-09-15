<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Inventory;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Create inventory items for the first (and only) user
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No user found. Please create a user first.');
            return;
        }

        // Create sample inventory items
        $inventoryItems = [
            [
                'name' => 'Dell Laptop - XPS 13',
                'description' => 'High-performance laptop for development work. 16GB RAM, 512GB SSD, Intel i7 processor.',
                'sku' => 'DELL-XPS13-001',
                'category' => 'Electronics',
                'quantity' => 5,
                'unit_price' => 1299.99,
                'min_quantity' => 2,
                'supplier' => 'Dell Technologies',
                'supplier_contact' => 'sales@dell.com',
            ],
            [
                'name' => 'Wireless Mouse - Logitech MX Master 3',
                'description' => 'Premium wireless mouse with ergonomic design and advanced tracking.',
                'sku' => 'LOG-MX3-001',
                'category' => 'Electronics',
                'quantity' => 12,
                'unit_price' => 99.99,
                'min_quantity' => 5,
                'supplier' => 'Logitech',
                'supplier_contact' => 'wholesale@logitech.com',
            ],
            [
                'name' => 'Office Chair - Ergonomic',
                'description' => 'Adjustable ergonomic office chair with lumbar support and headrest.',
                'sku' => 'CHAIR-ERG-001',
                'category' => 'Furniture',
                'quantity' => 8,
                'unit_price' => 299.99,
                'min_quantity' => 3,
                'supplier' => 'Office Furniture Co.',
                'supplier_contact' => 'orders@officefurniture.com',
            ],
            [
                'name' => 'A4 Paper - White',
                'description' => 'Premium white A4 paper, 80gsm, 500 sheets per ream.',
                'sku' => 'PAPER-A4-001',
                'category' => 'Office Supplies',
                'quantity' => 2,
                'unit_price' => 8.99,
                'min_quantity' => 10,
                'supplier' => 'Paper Supply Inc.',
                'supplier_contact' => 'sales@papersupply.com',
            ],
            [
                'name' => 'Blue Ink Pens - Pack of 12',
                'description' => 'Smooth-writing blue ink pens, 0.7mm tip, comfortable grip.',
                'sku' => 'PEN-BLUE-001',
                'category' => 'Office Supplies',
                'quantity' => 0,
                'unit_price' => 12.99,
                'min_quantity' => 5,
                'supplier' => 'Writing Supplies Ltd.',
                'supplier_contact' => 'orders@writingsupplies.com',
            ],
            [
                'name' => 'Microsoft Office 365 License',
                'description' => 'Annual subscription for Microsoft Office 365 Business Premium.',
                'sku' => 'MS-OFFICE365-001',
                'category' => 'Software',
                'quantity' => 15,
                'unit_price' => 150.00,
                'min_quantity' => 5,
                'supplier' => 'Microsoft',
                'supplier_contact' => 'volume@microsoft.com',
            ],
            [
                'name' => 'Monitor Stand - Adjustable',
                'description' => 'Height-adjustable monitor stand with cable management.',
                'sku' => 'STAND-MON-001',
                'category' => 'Furniture',
                'quantity' => 6,
                'unit_price' => 79.99,
                'min_quantity' => 3,
                'supplier' => 'Desk Accessories Co.',
                'supplier_contact' => 'sales@deskaccessories.com',
            ],
            [
                'name' => 'USB-C Hub - 7-in-1',
                'description' => 'Multi-port USB-C hub with HDMI, USB 3.0, SD card reader, and power delivery.',
                'sku' => 'HUB-USB7-001',
                'category' => 'Electronics',
                'quantity' => 1,
                'unit_price' => 89.99,
                'min_quantity' => 3,
                'supplier' => 'Tech Accessories Inc.',
                'supplier_contact' => 'wholesale@techaccessories.com',
            ],
            [
                'name' => 'Stapler - Heavy Duty',
                'description' => 'Heavy-duty stapler with 50-sheet capacity and built-in staple remover.',
                'sku' => 'STAPLER-HD-001',
                'category' => 'Office Supplies',
                'quantity' => 4,
                'unit_price' => 24.99,
                'min_quantity' => 2,
                'supplier' => 'Office Equipment Co.',
                'supplier_contact' => 'orders@officeequipment.com',
            ],
            [
                'name' => 'Projector - Portable',
                'description' => 'Portable HD projector with wireless connectivity and built-in speakers.',
                'sku' => 'PROJ-PORT-001',
                'category' => 'Electronics',
                'quantity' => 2,
                'unit_price' => 599.99,
                'min_quantity' => 1,
                'supplier' => 'AV Equipment Ltd.',
                'supplier_contact' => 'sales@avequipment.com',
            ]
        ];

        foreach ($inventoryItems as $itemData) {
            Inventory::create([
                'user_id' => $user->id,
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'sku' => $itemData['sku'],
                'category' => $itemData['category'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'min_quantity' => $itemData['min_quantity'],
                'supplier' => $itemData['supplier'],
                'supplier_contact' => $itemData['supplier_contact'],
                'needs_reorder' => $itemData['quantity'] <= $itemData['min_quantity'],
            ]);
        }

        $this->command->info('Sample inventory items created successfully!');
    }
}