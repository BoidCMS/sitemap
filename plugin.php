<?php defined( 'App' ) or die( 'BoidCMS' );
/**
 *
 * XML/TXT sitemap
 *
 * @package Plugin_Sitemap
 * @author Shuaib Yusuf Shuaib
 * @version 1.0.0
 */

global $App;
$App->set_action( 'install', 'sitemap_install' );
$App->set_action( 'uninstall', 'sitemap_uninstall' );
$App->set_action( [ 'create_success', 'update_success', 'delete_success' ], 'sitemap_generate' );
$App->set_action( 'admin', 'sitemap_admin' );

/**
 * Initialize Sitemap, first time install
 * @param string $plugin
 * @return void
 */
function sitemap_install( string $plugin ): void {
  global $App;
  if ( 'sitemap' === $plugin ) {
    $config = array();
    $config[ 'type' ] = 'xml';
    $config[ 'delete' ] = false;
    $App->set( $config, 'sitemap' );
  }
}

/**
 * Free database space, while uninstalled
 * @param string $plugin
 * @return void
 */
function sitemap_uninstall( string $plugin ): void {
  global $App;
  if ( 'sitemap' === $plugin ) {
    $config = $App->get( 'sitemap' );
    if (   $config[ 'delete' ]   ) {
      $xml = $App->root( 'sitemap.xml' );
      $txt = $App->root( 'sitemap.txt' );
      ( ! is_file( $xml ) ?: unlink( $xml ) );
      ( ! is_file( $txt ) ?: unlink( $txt ) );
    }
    $App->unset( 'sitemap' );
  }
}

/**
 * Admin settings
 * @return void
 */
function sitemap_admin(): void {
  global $App, $layout, $page;
  switch ( $page ) {
    case 'sitemap':
      $config = $App->get( 'sitemap' );
      $layout[ 'title' ] = 'Sitemap';
      $layout[ 'content' ] = '
      <form action="' . $App->admin_url( '?page=sitemap', true ) . '" method="post">
        <label for="type" class="ss-label">Sitemap Format</label>
        <select id="type" name="type" class="ss-select ss-mobile ss-w-6 ss-mx-auto">
          <option value="xml"' . ( $config[ 'type' ] === 'xml' ? ' selected' : '' ) . '>XML (sitemap.xml)</option>
          <option value="txt"' . ( $config[ 'type' ] !== 'xml' ? ' selected' : '' ) . '>TXT (sitemap.txt)</option>
        </select>
        <p class="ss-small ss-gray ss-mb-5">This option allows you to choose the type of sitemap you want to generate.</p>
        <label for="delete" class="ss-label">Delete Sitemap on Uninstall</label>
        <select id="delete" name="delete" class="ss-select ss-mobile ss-w-6 ss-mx-auto">
          <option value="true"' . ( $config[ 'delete' ] ? ' selected' : '' ) . '>Yes</option>
          <option value="false"' . ( ! $config[ 'delete' ] ? ' selected' : '' ) . '>No</option>
        </select>
        <p class="ss-small ss-gray ss-mb-5">This option determines whether the generated sitemap should be deleted or kept when the plugin is uninstalled.</p>
        <input type="hidden" name="token" value="' . $App->token() . '">
        <input type="submit" name="save" value="Save" class="ss-btn ss-mobile ss-w-5">
      </form>';
      if ( isset( $_POST[ 'save' ] ) ) {
        $App->auth();
        $config[ 'type' ] = ( $_POST[ 'type' ] ?? 'xml' );
        $config[ 'delete' ] = filter_input( INPUT_POST, 'delete', FILTER_VALIDATE_BOOL );
        if ( $App->set( $config, 'sitemap' ) ) {
          $App->alert( 'Settings saved successfully.', 'success' );
          $App->go( $App->admin_url( '?page=sitemap' ) );
        }
        $App->alert( 'Failed to save settings, please try again.', 'error' );
        $App->go( $App->admin_url( '?page=sitemap' ) );
      }
      require_once $App->root( 'app/layout.php' );
      break;
  }
}

/**
 * Sitemap generate
 * @return bool
 */
function sitemap_generate(): bool {
  global $App;
  $config = $App->get( 'sitemap' );
  if ( 'xml' === $config[ 'type' ] ) {
    $doc = sitemap_xml();
    $file = $App->root( 'sitemap.xml' );
    return ( bool ) $doc->save( $file );
  }
  $text = sitemap_txt();
  $file = $App->root( 'sitemap.txt' );
  return ( bool ) file_put_contents( $file, $text );
}

/**
 * Sitemap pages
 * @return array
 */
function sitemap_pages(): array {
  global $App;
  $pages = $App->data()[ 'pages' ];
  foreach ( $pages as $slug => $p ) {
    if ( '404' === $slug || ! $p[ 'pub' ] ) {
      unset( $pages[ $slug ] );
    }
  }
  return $pages;
}

/**
 * Sitemap XML
 * @return DOMDocument
 */
function sitemap_xml(): DOMDocument {
  global $App;
  $pages = sitemap_pages();
  $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
  $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  $xml .= '  <url>';
  $xml .= '    <loc>' . $App->url() . '</loc>';
  $xml .= '  </url>';
  foreach ( $pages as $slug => $page ) {
    $xml .= '<url>';
    $xml .= '  <loc>' . $App->url( $slug ) . '</loc>';
    $xml .= '  <lastmod>' . $page[ 'date' ] . '</lastmod>';
    $xml .= '</url>';
  }
  $xml .= '</urlset>';
  $doc = new DOMDocument();
  $doc->formatOutput = true;
  $doc->loadXML( $xml );
  return $doc;
}

/**
 * Sitemap TXT
 * @return string
 */
function sitemap_txt(): string {
  global $App;
  $pages = sitemap_pages();
  $sitemap  = $App->url() . PHP_EOL;
  foreach ( $pages as $slug => $page ) {
    $sitemap .= $App->url( $slug ) . PHP_EOL;
  }
  return $sitemap;
}
?>
