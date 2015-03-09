<?php namespace DeSmart\Pagination;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Pagination\Paginator as BasePaginator;

class Paginator extends BasePaginator {

  /**
   * @var \Illuminate\Routing\UrlGenerator
   */
  protected $urlGenerator;

  /**
    * @var \Illuminate\Routing\Router
   */
  protected $router;

  /**
    * @var boolean
   */
  protected $withQuery = true;

  /**
   * Configuration for assigned route
   *
   * @var array
   */
  protected $routeConfig;

  /**
   * Page range proximity 
   *
   * @var integer
   */
  protected $pagesProximity;

  /**
   * Cached pages range
   *
   * @var array
   */
  protected $pagesRange;

  /**
   * @param \Illuminate\Routing\UrlGenerator $generator
   */
  public function setUrlGenerator(UrlGenerator $generator) {
    $this->urlGenerator = $generator;
  }

  /**
   * @param \Illuminate\Routing\Router $router
   */
  public function setRouter(Router $router) {
    $this->router = $router;
  }

  /**
   * Pass to route query data
   *
   * @return \DeSmart\Pagination\Paginator
   */
  public function withQuery() {
    $this->withQuery = true;
  
    return $this;
  }

  /**
   * Don't pass query data to generated route
   *
   * @return \DeSmart\Pagination\Paginator
   */
  public function withoutQuery() {
    $this->withQuery = false;
  
    return $this;
  }

  /**
   * Set pages range proximity
   *
   * @param integer $proximity
   * @return \DeSmart\Pagination\Paginator
   */
  public function pagesProximity($proximity) {
    $this->pagesProximity = $proximity;
    $this->pagesRange = null;

    return $this;
  }

  /**
   * Bind route to generated pagination links
   *
   * @param \Illuminate\Routing\Route|string $route if string route with given name will be used
   * @param array $parameters
   * @param bool $absolute
   * @return \DeSmart\Pagination\Paginator
   */
  public function route($route, array $parameters = array(), $absolute = true) {
    $instance = null;
    $name = $route;

    if(true === is_object($route) && $route instanceof Route) {
      $instance = $route;
      $name = null;
    }

    $this->routeConfig = compact('instance', 'name', 'parameters', 'absolute');

    return $this;
  }

  /**
   * Use current route for generating url
   *
   * @TODO $route->parameters() can throw Exception if it has no parameters defined
   *       it should be handled, but Laravel UrlGenerator can't generate urls with extra params
   *       so maybe it's better to leave it that way.
   * @return \DeSmart\Pagination\Paginator
   */
  public function useCurrentRoute() {
    $route = $this->router->current();

    return $this->route($route, $route->parameters());
  }

  /**
   * Get a URL for a given page number.
   *
   * @param integer $page
   * @return string
   */
  public function getUrl($page) {

    if(null === $this->routeConfig) {
      return parent::getUrl($page);
    }

    $parameters = $this->routeConfig['parameters'];

    if(true === $this->withQuery) {
      $parameters = array_merge($parameters, $this->factory->getRequest()->query());
    }

    $parameters[$this->factory->getPageName()] = $page;
    $absolute = (null === $this->routeConfig['absolute']) ? true : $this->routeConfig['absolute'];

    // allow adding hash fragments to url
    $fragment = $this->buildFragment();
    $generated_route = $this->urlGenerator->route($this->routeConfig['name'], $parameters, $absolute, $this->routeConfig['instance']);

    return $generated_route.$fragment;
  }

  /**
   * Get pages range to be shown in template
   *
   * @return array
   */
  public function getPagesRange() {

    if(null !== $this->pagesRange) {
      return $this->pagesRange;
    }

    if(null === $this->pagesProximity) {
      $this->pagesRange = range(1, $this->getLastPage());
    }
    else {
      $this->pagesRange = $this->calculatePagesRange();
    }

    return $this->pagesRange;
  }

  /**
   * Calculate pages range for given proximity
   *
   * @return array
   */
  protected function calculatePagesRange() {
    $current_page = $this->getCurrentPage();
    $last_page = $this->getLastPage();
    $start = $current_page - $this->pagesProximity;
    $end = $current_page + $this->pagesProximity;

    if($start < 1) {
      $offset = 1 - $start;
      $start += $offset;
      $end += $offset;
    }
    else if($end > $last_page) {
      $offset = $end - $last_page;
      $start -= $offset;
      $end -= $offset;
    }

    if($start < 1) {
      $start = 1;
    }

    if($end > $last_page) {
      $end = $last_page;
    }

    return range($start, $end);
  }

  /**
   * Check if can show first page in template
   *
   * @return boolean
   */
  public function canShowFirstPage() {
    return false === array_search(1, $this->getPagesRange());
  }

  /**
   * Check if can show last page in template
   *
   * @return boolean
   */
  public function canShowLastPage() {
    return false === array_search($this->getLastPage(), $this->getPagesRange());
  }

}
