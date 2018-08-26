<?php

namespace Tests\Unit;

use ASCLicenses\Licenses;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /** @test */
    public function it_downloads_appraisers_file()
    {
        $records = (new Licenses())->all();
        
        $this->assertTrue(count($records) > 0);
    }

    /** @test */
    public function it_loads_appraiser()
    {
        $record = (new Licenses())->all()->fetchOne();

        $this->assertNotEmpty($record);
    }

    /** @test */
    public function it_filter_appraisers_by_state()
    {
        $record = (new Licenses())->all('CA')->fetchOne();

        $this->assertEquals('CA', $record['st_abbr']);
    }
}
