<?php
namespace login\user;
/*
 *
 * Colelction of users, from login or facebook
 *
 * @author caiofior
 */
class UserColl {
    /**
    * Contet of the collection
    * @var \login\user\Profile
    */
    protected $content;
    /**
   * Array of items
   * @var array
   */
    protected $items=array();
    /**
   * Instantiates the collection
   * @param \login\user\Profile $content Content base of the collection
   */
    public function __construct(\login\user\Profile $content) {
        $this->content = $content;
    }
    /**
     * Loads all elements
     * @param array $criteria
     */
    public function loadAll(array $criteria=null) {
        $loginColl = new \login\user\LoginColl($this->content->getDb());
        $loginColl->loadAll(array('profile_id'=>$this->content->getData('id')));
        foreach($loginColl->getItems() as $login) {
            $this->appendItem($login);
        }
        $facebookColl = new \login\user\FacebookColl($this->content->getDb());
        $facebookColl->loadAll(array('profile_id'=>$this->content->getData('id')));
        foreach($facebookColl->getItems() as $facebook) {
            $this->appendItem($facebook);
        }
    }
     /**
     * Append an item
     * @param \login\user\User $item
     */
    public function appendItem(& $item) {
       array_push($this->items, $item);
    }
    /**
   * Return the first item of the collection
   * @return \Content
   */
    public function getFirst() {
        if (!array_key_exists(0, $this->items)) {
            return new \login\user\Login($this->content->getDb());
        }
        return $this->items[0];
    }
    
}
