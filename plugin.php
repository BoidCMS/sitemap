<?php defined( 'App' ) or die( 'BoidCMS' );
/**
 *
 * XML Sitemap
 *
 * @package BoidCMS
 * @subpackage Sitemap
 * @author Shoaiyb Sysa
 * @version 1.0.0
 */

global $App;
$App->set_action( 'install', 'sitemap_init' );
$App->set_action( array( 'create_success', 'update_success', 'delete_success' ), 'sitemap_generate' );

/**
 * Initiate Sitemap
 * @return void
 */
function sitemap_init( string $plugin ): void {
  global $App;
  if ( 'sitemap' === $plugin ) {
    sitemap_generate();
  }
}

/**
 * Generate Sitemap
 * @return void
 */
function sitemap_generate(): void {
  global $App;
  $xml = sitemap_xml();
  $file = $App->root( 'sitemap.xml' );
  file_put_contents( $file, $xml );
}

/**
 * Sitemap XML
 * @return string
 */
function sitemap_xml(): string {
  global $App;
  $pages = $App->data()[ 'pages' ];
  $xml = '<?xml version="1.0" encoding="UTF-8"?>';
  $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  $xml .= '<url>';
  $xml .= '<loc>' . $App->url() . '</loc>';
  $xml .= '</url>';
  foreach ( $pages as $slug => $page ) {
    if ( $App->page( 'pub', $slug ) ) {
      $xml .= '<url>';
      $xml .= '<loc>' . $App->url( $slug ) . '</loc>';
      $xml .= '<lastmod>' . $App->page( 'date', $slug ) . '</lastmod>';
      $xml .= '</url>';
    }
  }
  $xml .= '</urlset>';
  $dom = new DOMDocument();
  $dom->formatOutput = true;
  $dom->loadXML( $xml );
  $xml = $dom->saveXML();
  $xml = trim( $xml );
  return $xml;
}
?>
