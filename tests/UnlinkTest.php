<?php
namespace mimus\tests {

    use mimus\tests\classes\FooBar;
	use \mimus\Double as double;

	final class UnlinkTest extends \PHPUnit\Framework\TestCase {

		public function testUnlink() {

        $fb = new FooBar();

        $builder = double::class(FooBar::class);
        $builder->rule("bar")
        ->expects()
        ->returns('foo');

        $this->assertEquals('foo', $fb->bar());

        var_dump(double::exists(FooBar::class));

        double::unlink(FooBar::class);
        //double::clear();
        //$builder->reset('bar');
        var_dump(double::exists(FooBar::class));
        $this->assertEquals('bar', $fb->bar());

		}
	}
}
