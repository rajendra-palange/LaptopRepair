<?php

namespace Drupal\Tests\admin_toolbar\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Test the functionality of admin toolbar search.
 *
 * @group admin_toolbar
 */
class AdminToolbarSearchTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'admin_toolbar',
    'admin_toolbar_tools',
    'node',
    'field_ui',
    'block',
  ];

  /**
   * The admin user for tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    $this->drupalPlaceBlock('local_tasks_block');

    $this->adminUser = $this->drupalCreateUser([
      'access toolbar',
      'administer menu',
      'access administration pages',
      'administer site configuration',
      'administer content types',
      'administer node fields',
    ]);
  }

  /**
   * Tests search functionality.
   */
  public function testSearchFunctionality() {

    $search_tab = '#toolbar-item-administration-search';
    $search_tray = '#toolbar-item-administration-search-tray';

    $this->drupalLogin($this->adminUser);
    $this->assertSession()->responseContains('admin.toolbar_search.css');
    $this->assertSession()->responseContains('admin_toolbar_search.js');
    $this->assertSession()->waitForElementVisible('css', $search_tab)->click();
    $this->assertSession()->waitForElementVisible('css', $search_tray);

    $this->assertSuggestionContains('basic', 'admin/config/system/site-information');

    // Rebuild menu items.
    drupal_flush_all_caches();

    $this->drupalGet('admin/structure/types/manage/article/fields');
    $this->assertSession()->waitForElementVisible('css', $search_tray);

    $this->assertSuggestionContains('article manage fields', '/admin/structure/types/manage/article/fields');

    $suggestions = $this->assertSession()
      ->waitForElementVisible('css', 'ul.ui-autocomplete');

    // Assert there is only one suggestion with a link to /admin/structure/types/manage/article/fields.
    $count = count($suggestions->findAll('xpath', '//span[contains(text(), "/admin/structure/types/manage/article/fields")]'));
    $this->assertEquals(1, $count);
  }

  /**
   * Assert that the search suggestions contain a given string with a given input.
   *
   * @param string $search
   *   The string to search for.
   * @param string $contains
   *   Some HTML that is expected to be within the suggestions element.
   */
  protected function assertSuggestionContains($search, $contains) {
    $this->assertSession()
      ->elementExists('css', '#admin-toolbar-search-input')
      ->setValue($search);
    $suggestions_markup = $this->assertSession()
      ->waitForElementVisible('css', 'ul.ui-autocomplete')
      ->getHtml();
    $this->assertContains($contains, $suggestions_markup);
  }

}
