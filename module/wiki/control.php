<?php

class wiki extends control {
    
    protected $pages = null;
    protected $productId = null;
    protected $revision = null;

    public function __construct($moduleName = '', $methodName = '') {
        parent::__construct($moduleName, $methodName);
    }
    
    public function commonAction($productID)
    {
        $this->loadModel('product');
        $this->view->product = $this->product->getById($productID);
        $this->view->position[] = html::a($this->createLink('product', 'browse', "productID={$this->view->product->id}"), $this->view->product->name);
        $this->product->setMenu($this->product->getPairs(), $productID);
    }

    /**
     * 显示每个产品的wiki封面页
     * @param int $productId 参数的值自动从第一个参数映射
     */
    public function index($productId){
        $this->commonAction($productId);
        //$product_id = fixer::input('get')->cleanINT('productId')->get();
        //根据产品id查询page表，找到该产品的wiki封面页，若不存在，则创建一个默认封面页
        $cover_page = $this->wiki->getProductIndex($productId);

        if(is_object($cover_page)){//找到封面
            $this->revision = $this->wiki->getRevision($productId);
        } else {//否则创建默认封面
           /* $page = new WikiPage;
            //Make a revision for the page
            $revision = $page->makeRevision();
            //Fill in the default content
            $revision->setContent(lang('wiki default page content'));
            //Set the name of the page
            $revision->setName(lang('wiki default page name'));*/
        }
        
        if(!is_object($this->revision))
            die(js::locate(inlink('view', "product=".$this->productId), 'parent'));
       
        $this->pages = $cover_page;
        //向视图传递变量
        $this->view->pages = $this->pages;
        
        $this->app->loadClass('textile');
        $textile = new Textile();
        $this->revision->content = $this->wiki->addLinks($textile->TextileRestricted($this->revision->content, false, false)); 
        $this->view->revision = $this->revision;

        $products = $this->product->getPairs();

        $this->view->header->title = $products[$productId];
        $this->view->position[]    = $this->lang->wiki->common;
        $this->view->productId = $productId;
        
        $this->display();

    }
    
    /**
     * 构建编写wiki的界面并保存
     * @param type $productId
     */
    public function create($productId){
        $this->commonAction($productId);

        if(!empty($_POST))
        {
            $page_id = $this->wiki->create($productId);
            if(dao::isError())
                die(js::error(dao::getError()));
            
            die(js::locate($this->createLink($this->moduleName, 'view', "pageId=$page_id"), 'parent'));//新建时，默认修订版本为1
        }

        //设置页面标题及当前路径
        $products = $this->product->getPairs();
        $this->view->header->title = $products[$productId];
        $this->view->header->title .= ' ' . $this->lang->wiki->create;
        $this->view->position[]    = $this->lang->wiki->common;;

        $this->display();
    }
    
    /**
     * 查看wiki指定页面的指定版本
     * @param int $pageId 页面id
     * @param int $revId 版本号
     */
    public function view($pageId, $revId = 1){
        if(!is_numeric($pageId) || !$pageId){
            die(js::error('请指定要访问的页面！').js::locate('back'));
        }
        
        $page = $this->wiki->getPageById($pageId, $revId);
        var_dump($page->data);
        exit;
        if (!$page->name) {
            die(js::error('抱歉您指定的页面不存在！') . js::locate('back'));
        }
        
        var_dump($page->name.$page->content);
        echo $pageId;
        echo $revId;
    }
}