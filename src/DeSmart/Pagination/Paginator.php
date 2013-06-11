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
   * @var array|null
   */
  protected $route;

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
    return $this->route($this->router->currentRouteName(), $this->router->getCurrentRoute()->getParameters(), true);
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

}
