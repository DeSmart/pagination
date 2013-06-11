<?php namespace DeSmart\Pagination;

use Illuminate\Support\ServiceProvider;

class PaginationServiceProvider extends ServiceProvider {

  /**
   * Indicates if loading of the provider is deferred.
   *
   * @var bool
   */
  protected $defer = true;

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register() {
    $this->app['paginator'] = $this->app->share(function($app) {
      $paginator = new Environment($app['request'], $app['view'], $app['translator']);
      $paginator->setViewName($app['config']['view.pagination']);
      $paginator->setUrlGenerator($app['url']);
      $paginator->setRouter($app['router']);

      return $paginator;
    });
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides() {
    return array('paginator');
  }

}
