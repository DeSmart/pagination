<?php namespace DeSmart\Pagination;

use Illuminate\Pagination\Paginator as BasePaginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\Router;

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
   * Route assigned to paginator
   *
   * @var array
   */
  protected $route;

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
   * @param string $name
   * @param array $parameters
   * @param boolean $absolute
   * @return \DeSmart\Pagination\Paginator
   */
  public function route($name, array $parameters = array(), $absolute = true) {
    $this->route = compact('name', 'parameters', 'absolute');

    return $this;
  }

  /**
   * Use current route for generating url
   *
   * @return \DeSmart\Pagination\Paginator
   */
  public function useCurrentRoute() {
    // @TODO \Illuminate\Routing\Router::currentRouteName() doesn't exist
    return $this->route($this->router->currentRouteName(), $this->router->current()->getParameters(), true);
  }

  /**
   * Get the pagination links view.
   *
   * @param string $view
   * @return \Illuminate\View\View
   */
  public function links($view = null) {
    return $this->env->getPaginationView($this, $view);
  }

  /**
   * Get a URL for a given page number.
   *
   * @param integer $page
   * @return string
   */
  public function getUrl($page) {

    if(null === $this->route) {
      return parent::getUrl($page);
    }

    $parameters = $this->route['parameters'];

    if(true === $this->withQuery) {
      $parameters = array_merge($parameters, $this->env->getRequest()->query());
    }

    $parameters[$this->env->getPageName()] = $page;

    return $this->urlGenerator->route($this->route['name'], $parameters, $this->route['absolute']);
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
