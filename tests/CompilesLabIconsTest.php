<?php

declare(strict_types=1);


use MallardDuck\LucideIcons\BladeLucideIconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Orchestra\Testbench\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class CompilesLabIconsTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_compiles_a_single_anonymous_component()
    {
        $result = svg('lucidelab-avocado')->toHtml();
        $this->assertMatchesXmlSnapshot($result);
    }

    /** @test */
    public function it_can_add_classes_to_icons()
    {
        $result = svg('lucidelab-bacon', 'w-6 h-6 text-gray-500')->toHtml();
        $this->assertMatchesXmlSnapshot($result);
    }

    /** @test */
    public function it_can_add_styles_to_icons()
    {
        $result = svg('lucide-lab.whale-narwhal', ['style' => 'color: #555'])->toHtml();
        $this->assertMatchesXmlSnapshot($result);
    }

    protected function getPackageProviders($app)
    {
        return [
            BladeIconsServiceProvider::class,
            BladeLucideIconsServiceProvider::class,
        ];
    }
}
