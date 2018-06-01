<?php
namespace app\site\controller;

class Index extends Base
{

    /**
     * 初始化操作
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageSize = config('paginate.list_rows');
    }

    /**
     * 首页
     * @param p
     * @param article_title
     */
    public function index($p=1,$article_title='')
    {      
        $article_title = str_content_replace($article_title);
        $where['a.article_title'] = ['like',"%{$article_title}%"];
        $allart = $this->Article->getAllArticleByWhere($p,$where,$this->pageSize);
        if(empty($allart))
        {
            $this->redirect('/error');
        }
        $count= count($allart);
		$page = ceil($count/$this->pageSize);
        if(request()->isAjax())
        {
            return $allart;
        }

		$this->assign('allart',$allart);
		$this->assign('p',$p);
		$this->assign('page',$page);
        $this->assign('title',$article_title);
    	return $this->fetch();
	}

    /**
     * 关于页面
     * @param id
     */
    public function about($id=7){
        $where['article_id'] = $id;
        $content = $this->Article->getArticleByWhere($where);
        if(empty($content))
        {
            $this->redirect('/error');
        }
        $content['des'] = $this->Article->getDes()->where("pid={$id}")->select();
        $this->assign('content',$content);
        return $this->fetch('index/about');
    }

    /**
     * 文章详情页
     * @param $id
     */
    public function article($id)
    { 
        $where['article_id'] = $id;
        $content = $this->Article->getArticleByWhere($where);
        if(empty($content))
        {
            $this->redirect('/error');
        }
        $content['des'] = $this->Article->getDes()->where('article_id',$id)->select();
        if($content['show_type']==1)
        {
            return $this->fetch('index/about',['content'=>$content]);
        }
        $arr = ishav_str_array(',',$content['tag_ids']);
        if(empty($arr))
        {
            $str = ''; 
        }elseif(empty($arr[1]))
        {
            $str = '<span title="Tags" class="am-icon-tag"> &nbsp;</span>'; 
        }else
        {
            $str = '<span title="Tags" class="am-icon-tags"> &nbsp;</span>'; 
        }
        foreach ($arr as $k=>$v){
            $tag = $this->ArticleTags::getbyid($v)['value'];
            $str .= "<a href='/tag/".$tag."'>".$tag."</a> ,";
        }
        $content['tags']=rtrim($str, ",");
        $content['lastid'] = $this->Article->getLastidById($id);
        $content['nextid'] = $this->Article->getNextidById($id);
        $this->assign('content',$content);
        return $this->fetch();
    }

    /**
     * 文章归档页
     */
    public function timeline()
    {
        $file = $this->Article->getAllArticleByYear();
        $this->assign('file',$file);
        return $this->fetch();
    }

}