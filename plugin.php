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
    $App->unset( 'sitemap' );
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
  $dom = new DOMDocument();
  $dom->formatOutput = true;
  $dom->loadXML( $xml );
  return $xml;
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
