<?php
/**
 * wiki模型类
 *
 * @package     wiki
 * @version     $Id$
 */
class wikiModel extends model
{
    //const TBL_WIKI_PAGE = 'wiki_pages';
    //const TBL_WIKI_REV = 'wiki_revisions';
    
    /**
    * Cached array of all revisions of this page
    *
    * @var array
    */
    protected $revisions = array(); 

    /**
    * The current revision of this page
    *
    * @var array
    */
    protected $cur_revision;

    /**
    * The new revision of this page
    *
    * @var array
    */
    protected $new_revision;
    
    /**
   * Get the index page of a product
   * 
   * @param int Instance of project
   * @return
   */
	public function getProductIndex($product_id)
	{

        if(!$product_id = intval($product_id))
            return null;
        
        return $this->dao->select('id,revision')->from(TABLE_WIKI_PAGE)
                ->where('product_id')->eq($product_id)
                ->andWhere('is_index')->eq('1')
                ->fetch();
	}
    
    /**
     * 获取指定的版本
     * 
     * @param null|int $revision 为空时获取最新版本，为整数时获取指定版本
     * @return mixed
     */
    function getRevision($page_id, $revision = null) {
        if(!$page_id = intval($page_id))
            return null;
        
        if ($revision == null && $this->cur_revision) {
            return $this->cur_revision;
        } else if (isset($this->revisions[$revision])) {
            return $this->revisions[$revision];
        } 

        $dao = $this->dao->select('*')->from(TABLE_WIKI_REV)
                ->where('page_id')->eq($page_id);

        if ($revision === null) {
            $dao->orderBy('revision DESC');
            return $this->cur_revision = $dao->fetch();
        } else {
            $revision = (int) $revision;
            $this->dao->andWhere('revision')->eq($revision);
            return $this->revisions[$revision] = $dao->fetch();
        } // if
    }
    

     /**
     * Create a wiki reversion.
     * 
     * @access public
     * @return bool
     */
    public function create($productId){
        //先在page中加入版本索引，然后保存revision
        //$product_id = fixer::input('get')->cleanINT('productId')->get();
        if(!$productId)
            return false;
        
        $page_id = $this->makeRevision($productId);
        if(!$page_id)
            return false;
        
        $revision = fixer::input('post')
            ->stripTags('name,content')
            ->remove('published')
             ->setDefault('page_id', $page_id)
             ->setDefault('revision', 1)//TODO:定义常量
             ->setDefault('created_by_id', $this->app->user->id)
             ->setDefault('created_on', time())
             ->setDefault('product_id', $productId)
            ->get();
        $this->dao->insert(TABLE_WIKI_REV)
            ->data($revision)
            ->autoCheck()
            ->batchCheck('name,content', 'notempty')
            ->exec();
        
        if (dao::isError())
            return false;
        
        return $page_id;
        
    }
    
    /**
     * 创建Rev版本号
     * 
     * @return int|boolean
     */
    public function makeRevision($productId) {
        $page = fixer::input('post')
                ->remove('name,content')
                ->stripTags('published')
                ->setIF($this->post->published != '1', 'published', '0')
                ->setDefault('revision', 1)
                ->setDefault('product_id', $productId)
                ->get();

        $this->dao->insert(TABLE_WIKI_PAGE)
                ->data($page)
                ->exec();

        if (dao::isError())
            return false;

        return $this->dao->lastInsertID();
    }
    
   /**
   * Build revision history of a page
   * 
   * @param mixed $id
   * @param mixed $project
   * @return
   */
	function buildPageHistory($id, Project $project)
	{
		return self::findAll(array( 'conditions' => array('`page_id` = ? AND `project_id` = ?', $id, $project->getId()), 'order' => '`revision` DESC'));
	}
    
    /**
     * 查找wiki指定页面的指定版本
     * 
     * @param mixed Page Id
     * @param mixed Active project
     * @return
     */
    function getPageById($pageId, $revId = 1) {
        $pageId = intval($pageId);
        $revId = intval($revId);
        if(!$pageId)
            return false;

        //根据pageId查找对应的产品信息？
        //再根据rev查找指定版本，若未指定rev，则查找最新版本
        $page_rev = $this->dao->select('p.product_id, p.published, r.name, r.content, r.created_on, r.created_by_id')
                ->from(TABLE_WIKI_REV)->alias('r')
                ->leftJoin(TABLE_WIKI_PAGE)->alias('p')
                ->on('r.page_id=p.id')
                ->where('p.id')->eq($pageId);

        if($revId)
             $page_rev->andWhere('r.revision = ' . $revId);

        $page_rev->fetch();
        //var_dump($page_rev);
        return $page_rev;
    }
  
  /**
   * Get the sidebar for a project
   * 
   * @param mixed $project
   * @return
   */
	function getProjectSidebar($project = null)
	{
		$params = array(
			'conditions'	=>	array(
													'project_id = ? AND project_sidebar = 1',
													(instance_of($project, 'Project') ? $project->getId() : 0)
													)
							);
		
		return parent::findOne($params);
	}
	
  /**
   * Get a list of pages for a project
   * 
   * @param mixed $project
   * @return
   */
  function getPagesList(Project $project)
  {
    $sql = 'SELECT p.id, r.name FROM ' . Wiki::instance()->getTableName(true) . ' AS p, ' . Revisions::instance()->getTableName(true) . ' AS r WHERE p.project_id = ' . $project->getId() . ' AND p.id = r.page_id AND r.revision = p.revision AND p.project_sidebar = 0 ORDER BY 2'; 
    $return = array();
      foreach(((array) DB::executeAll($sql)) as $page){
        $return[] = array(
          'name' => $page['name'],	
	  'view_url' => get_url('wiki', 'view', array('id' => $page['id'])
        )
      );
    }
    return $return;
  }

    /**
    * Return array of all pages for project
    *
    * @param Project
    * @return ProjectLinks
    */
    static function getAllProjectPages(Project $project) {
      trace(__FILE__,'getAllProjectPages():begin');
      
      $conditions = array('`project_id` = ?', $project->getId());
      
      return self::findAll(array(
        'conditions' => $conditions,
        'order' => '`id` ASC',
      )); // findAll
      trace(__FILE__,'getAllProjectPages():end');
    } // getAllProjectPages
    
        //////////////////////////////////////////
    // System
    //////////////////////////////////////////
	
    /**
    * Delete page & its revisions
    * 
    * @return
    */
    function delete() {
      $revisions = (array) Revisions::buildPageHistory($this->getId(), $this->getProject());
      foreach($revisions as $revision) {
        $revision->delete();
      }
      return parent::delete();
    } // delete
    
       /**
  * This function will add hyperlinks to strings that look like links
  *
  * @param string $text
  * @return $text with possibly hyperlinks
  */
  function addLinks(&$text) {
    // The following searches for strings that look like links and auto-links them
    $search = array(
        '/(?<!")(http:\/\/[^\s\"<]*)/',
        '/[^\/](www\.[^\s<]*)/'
    );
    $replace = array(
        "<a href=\"$1\" rel=\"nofollow\">$1</a>",
        " <a href=\"http://$1\" rel=\"nofollow\">$1</a>"
    );
    $text = preg_replace($search,$replace,$text);

    return $text;
  }



    /**
     * Update a group.
     * 
     * @param  int    $groupID 
     * @access public
     * @return void
     */
    public function update($groupID)
    {
        $group = fixer::input('post')->specialChars('name, desc')->get();
        return $this->dao->update(TABLE_GROUP)->data($group)->batchCheck($this->config->group->edit->requiredFields, 'notempty')->where('id')->eq($groupID)->exec();
    }

    /**
     * Get group pairs.
     * 
     * @access public
     * @return array
     */
    public function getPairs()
    {
        return $this->dao->findByCompany($this->app->company->id)->fields('id, name')->from(TABLE_GROUP)->fetchPairs();
    }

    /**
     * Get group by id.
     * 
     * @param  int    $groupID 
     * @access public
     * @return object
     */
    public function getByID($groupID)
    {
        return $this->dao->findById($groupID)->from(TABLE_GROUP)->fetch();
    }

}
