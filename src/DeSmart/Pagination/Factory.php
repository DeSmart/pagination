<?php namespace DeSmart\Pagination;

use Illuminate\Pagination;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\Router;

class Factory extends Pagination\Factory {

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
  public function make(array $items, $total, $perPage = null) {
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
      $page = $this->router->current()
        ->parameter($this->pageName, null);
    }

    if(null === $page) {
      $page = $this->request->query->get($this->pageName, 1);
    }

    if ($page < 1 || false === filter_var($page, FILTER_VALIDATE_INT)) {
      return 1;
    }

    return $page;
  }

  /**
   * Get the pagination view.
   *
   * @param \Illuminate\Pagination\Paginator $paginator
   * @param string $view view name
   * @return \Illuminate\View\View
   */
  public function getPaginationView(\Illuminate\Pagination\Paginator $paginator, $view = null) {
    $data = array('environment' => $this, 'paginator' => $paginator);

    return $this->view->make($view ?: $this->getViewName(), $data);
  }

}
