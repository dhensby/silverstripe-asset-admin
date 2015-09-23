<?php
use SilverStripe\Forms\AssetGalleryField;

/**
 * @mixin PHPUnit_Framework_TestCase
 */
class AssetGalleryFieldTest extends SapphireTest {
	/**
	 * @var string
	 */
	public static $fixture_file = 'asset-gallery-field/tests/AssetGalleryFieldTest.yml';

	public function testHasAttributes() {
		$field = new AssetGalleryField("Files1");

		new Form(
			new AssetGalleryFieldTestController(),
			'Foo',
			new FieldList([
				$field
			]),
			new FieldList()
		);

		$markup = $field->Field();

		$this->assertContains("name='Files1'", $markup);
		$this->assertContains("data-asset-gallery-data-url=", $markup);
		$this->assertContains("data-asset-gallery-update-url=", $markup);
		$this->assertContains("data-asset-gallery-delete-url=", $markup);
	}

	/**
	 * @param string $name
	 * @param string $form
	 *
	 * @return AssetGalleryField
	 */
	protected function getNewField($name = 'Files1', $form = 'Form1') {
		$field = new AssetGalleryField($name);

		new Form(
			new AssetGalleryFieldTestController(),
			$form,
			new FieldList($field),
			new FieldList()
		);

		return $field;
	}

	/**
	 * @test
	 */
	public function itDefinesInitialData() {
		$this->objFromFixture('File', 'File2');

		$field = $this->getNewField();
		$field->setCurrentPath('Folder2');

		$field->Field();

		$scripts = Requirements::get_custom_scripts();

		$this->assertContains('"title":"The Second File"', $scripts);
		$this->assertNotContains('"title":"The First File"', $scripts);
	}

	/**
	 * @test
	 */
	public function itGetsNewData() {
		$this->objFromFixture('File', 'File1');

		$field = $this->getNewField();
		$field->setCurrentPath('Folder1');

		$response = $field->data(new SS_HTTPRequest('GET', 'http://example.com'));

		$files = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('files', $files);
		$this->assertCount(1, $files['files']);
		$this->assertEquals('The First File', $files['files'][0]['title']);
	}

	/**
	 * @test
	 */
	public function itFiltersData() {
		$this->objFromFixture('File', 'File2');
		$this->objFromFixture('File', 'File3');

		$field = $this->getNewField();
		$field->setCurrentPath('Folder2');

		$request = new SS_HTTPRequest('GET', 'http://example.com');

		$response = $field->data($request);
		$files = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('files', $files);
		$this->assertCount(2, $files['files']);

		$request = new SS_HTTPRequest('GET', 'http://example.com', array('name' => 'Third'));

		$response = $field->data($request);
		$files = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('files', $files);
		$this->assertCount(1, $files['files']);
		$this->assertEquals('The Third File', $files['files'][0]['title']);
	}
}

class AssetGalleryFieldTestController extends ContentController {

}