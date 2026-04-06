<?php

namespace Tests\Feature\Services;

use App\Exceptions\DomainException;
use App\Models\BudgetCode;
use App\Models\Department;
use App\Models\Division;
use App\Models\KPISection;
use App\Models\KPIWorkPlan;
use App\Models\Section;
use App\Models\WorkplanBudgetItem;
use App\Services\WorkplanImportService\WorkplanImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use SplTempFileObject;

class WorkplanImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WorkplanImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkplanImportService::class);
    }

    private function createCsvFile(array $data): UploadedFile
    {
        $file = tmpfile();
        $path = stream_get_meta_data($file)['uri'];
        
        $handle = fopen($path, 'w');
        
        // Write standard 2 header rows
        fputcsv($handle, ['CODE','NAME','ACTIVEFLAG','INCHARGECODE','REMARKS','Goods Code','AC Code','Price','1.JAN','','2.FEB','','3.MAR','','4.APR','','5.MAY','','6.JUN','','7.JUL','','8.AUG','','9.SEP','','10.OCT','','11.NOV','','12.DEC',''], ';');
        fputcsv($handle, ['','','','','','','','','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount','Qty','Amount'], ';');
        
        // Write data
        foreach ($data as $row) {
            fputcsv($handle, $row, ';');
        }
        
        fclose($handle);

        return new UploadedFile($path, 'test.csv', 'text/csv', null, true);
    }

    public function test_import_skips_when_activeflag_is_not_1()
    {
        // 32 columns required based on code structure
        $data = [
            ['BGT01', 'Test Name', '0', 'SEC01', 'Remarks', '', '', '1000', '1','1000', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0']
        ];
        
        $file = $this->createCsvFile($data);
        
        $result = $this->service->importWorkplanBudget($file);

        $this->assertEquals(0, $result['processed']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertDatabaseCount('workplan_budget_items', 0);
    }

    public function test_import_skips_when_price_less_than_one()
    {
        $data = [
            ['BGT01', 'Test Name', '1', 'SEC01', 'Remarks', '', '', '0', '1','1000', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0']
        ];
        
        $file = $this->createCsvFile($data);
        
        $result = $this->service->importWorkplanBudget($file);

        $this->assertEquals(0, $result['processed']);
        $this->assertEquals(1, $result['skipped']);
    }

    public function test_import_creates_kpi_and_budget_item_for_section()
    {
        // Prepare organization struct
        $division = Division::factory()->create(['code' => 'DIV01']);
        $department = Department::factory()->create(['code' => 'DEP01', 'division_id' => $division->id]);
        $section = Section::factory()->create(['code' => 'SEC01', 'department_id' => $department->id]);
        
        BudgetCode::factory()->create(['budget_code' => 'BGT01']);

        $data = [
            ['BGT01', 'Makan Siang', '1', 'SEC01', 'Note A', 'STK01', '', '1000.50', '2','2001', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0', '0','0']
        ];
        
        $file = $this->createCsvFile($data);
        
        $result = $this->service->importWorkplanBudget($file);

        $this->assertEquals(1, $result['processed']);
        
        // Assert structure
        $this->assertDatabaseHas('kpi_section', ['section_id' => $section->id]);
        
        $kpiSection = KPISection::where('section_id', $section->id)->first();
        $this->assertDatabaseHas('kpi_workplans', [
            'kpi_id'   => $kpiSection->id,
            'kpi_type' => 'section'
        ]);

        $kpiWorkplan = KPIWorkPlan::where('kpi_id', $kpiSection->id)->first();
        
        $this->assertDatabaseHas('workplan_budget_items', [
            'kpi_workplan_id'  => $kpiWorkplan->id,
            'budget_code'      => 'BGT01',
            'price_estimation' => 1000.5,
            'stock_code'       => 'STK01',
            'activity_jan'     => 2,
            'activity_feb'     => 0,
        ]);
    }
}
