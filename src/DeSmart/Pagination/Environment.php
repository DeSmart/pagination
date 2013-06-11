<?php namespace DeSmart\Pagination;

use Illuminate\Pagination\Environment as BaseEnvironment;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\Router;

class Environment extends BaseEnvironment {

  /**
   * @var \Illuminate\Routing\UrlGenerator
   */
  protected $urlGenerator;

  /**
    * @var \Illuminate\Routing\Router
   */
  protected $router;

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
   * Get a new paginator instance.
   *
   * @param array $items
   * @param integer $total
   * @param integer $perPage
   * @return \DeSmart\Pagination\Paginator
   */
  public function make(array $items, $total, $perPage) {
    $paginator = new Paginator($this, $items, $total, $perPage);
    $paginator->setUrlGenerator($this->urlGenerator);
    $paginator->setRouter($this->router);

    return $paginator->setupPaginationContext();
  }

  /**
   * Get the number of current page.
   *
   * @return integer
   */
  public function getCurrentPage() {
    $page = (int) $this->currentPage;

    if(true === empty($page)) {
      $page = $this->router->getCurrentRoute()->getParameter($this->pageName, null);
    }

    if(null === $page) {
      $page = $this->request->query->get($this->pageName, 1);
    }

    if ($page < 1 || false === filter_var($page, FILTER_VALIDATE_INT)) {
      return 1;
    }

    return $page;
  }

}
