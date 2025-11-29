<?php

namespace Database\Seeders;

use App\Models\BudgetCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BudgetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => '1',
                'name' => 'Material',
                'level' => 1,
                'sort_order' => 1,
                'children' => [
                    [
                        'code' => '1.1',
                        'name' => 'Material Consumption',
                        'level' => 2,
                        'sort_order' => 1,
                    ],
                    [
                        'code' => '1.2',
                        'name' => 'Material Procurement Planning',
                        'level' => 2,
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'code' => '2',
                'name' => 'Inventory',
                'level' => 1,
                'sort_order' => 2,
                'children' => [
                    [
                        'code' => '2.1',
                        'name' => 'Inventory Consumption',
                        'level' => 2,
                        'sort_order' => 1,
                    ],
                    [
                        'code' => '2.2',
                        'name' => 'Inventory Procurement Planning',
                        'level' => 2,
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'code' => '3',
                'name' => 'Services',
                'level' => 1,
                'sort_order' => 3,
                'children' => [
                    [
                        'code' => '3.1',
                        'name' => 'Services Consumption',
                        'level' => 2,
                        'sort_order' => 1,
                    ],
                    [
                        'code' => '3.2',
                        'name' => 'Services Procurement Planning',
                        'level' => 2,
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'code' => '4',
                'name' => 'Turn Around',
                'level' => 1,
                'sort_order' => 4,
                'children' => [
                    [
                        'code' => '4.1',
                        'name' => 'Turn Around Material Consumption & Procurement Planning',
                        'level' => 2,
                        'sort_order' => 1,
                    ],
                    [
                        'code' => '4.2',
                        'name' => 'Turn Around Inventory Consumption & Procurement Planning',
                        'level' => 2,
                        'sort_order' => 2,
                    ],
                    [
                        'code' => '4.3',
                        'name' => 'Turn Around Services Consumption & Procurement Planning',
                        'level' => 2,
                        'sort_order' => 3,
                    ],
                ]
            ],
            [
                'code' => '5',
                'name' => 'Investment',
                'level' => 1,
                'sort_order' => 5,
                'children' => [
                    [
                        'code' => '5.1',
                        'name' => 'Investment Planning',
                        'level' => 2,
                        'sort_order' => 1,
                    ],
                ]
            ],
            [
                'code' => '6',
                'name' => 'Carry Over',
                'level' => 1,
                'sort_order' => 6,
                'children' => [
                    [
                        'code' => '6.1',
                        'name' => 'Carry Over Program Consumption & Procurement Planning',
                        'level' => 2,
                        'sort_order' => 1,
                    ],
                ]
            ],
            [
                'code' => '7',
                'name' => 'Multi Years',
                'level' => 1,
                'sort_order' => 7,
                'children' => [
                    [
                        'code' => '7.1',
                        'name' => 'Multi Years Program Consumption & Procurement Planning',
                        'level' => 2,
                        'sort_order' => 1,
                    ],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            // Upsert parent by unique code (create once, update on subsequent runs)
            $parent = BudgetCategory::updateOrCreate(
                ['code' => $categoryData['code']],
                array_merge($categoryData, ['parent_id' => null])
            );

            // Upsert children and ensure parent_id is set
            foreach ($children as $childData) {
                $childDataWithParent = array_merge($childData, ['parent_id' => $parent->id]);
                BudgetCategory::updateOrCreate(
                    ['code' => $childDataWithParent['code']],
                    $childDataWithParent
                );
            }
        }
    }
}
