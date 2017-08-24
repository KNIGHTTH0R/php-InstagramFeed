<?php

/**
 * InstagramFeed generates a HTML representation of an Instagram feed
 * without needing to use the official API.
 *
 * Class Documentation: https://github.com/benjihughes/php-InstagramFeed
 * Example/Explanation: https://benjaminhughes.co.uk/blog/web/create-instagram-feed-for-website/
 * 
 * @author Benjamin Hughes
 * @since 24/08/17
 * @version 1.4
 */


class InstagramFeed
{
    
    const FEED_URL = 'https://www.instagram.com/';
    const QUERY_STRING = '/?__a=1';
    
    private $_username;         // string: Account to pull information from (must be public).
    private $_mediaLimit;       // int: Amount of media items to return.
    private $_showProfileInfo;  // boolean: Display username, profile / biography photo.
    private $_wrapHtml;         // array: Array with start/end html to wrap returned markup.
    private $_wrapHtmlItems;    // array: Array with start/end html to wrap each item markup.
    private $_showLikes;        // boolean: Return likes for each media item.
    private $_likesLabel;       // string: Label to display alongside likes.
    private $_showError;        // boolean: Output the error message as html.
    
    // Default config is to be merged with user values.
    private $_defaultConfig = array(
        'mediaLimit' => 3,
        'showProfileInfo' => true,
        'wrapHtml' => array('start' => '<div>', 'end' => '</div>'),       
        'wrapHtmlItems' => array('start' => '<div>', 'end' => '</div>'),       
        'showLikes' => true,
        'likesLabel' => 'likes',
        'showError' => true,
    );
    
    
   /**
     * Default constructor.
     *
     * @param string $username Instagram account username
     * @param array $config Array of configuration options
     *
     * @return void
     *
     */
    public function __construct($username, $config = '')
    {
        $this->setUsername($username);
        
        // Update default config if specified.
        if (is_array($config)) {
            
            // Merge with default config to override values that were specified
            $config = array_merge($this->_defaultConfig, $config);
            
        } else {
            $config = $this->_defaultConfig;
        }
        
        // Parse config values.
        $this->setMediaLimit($config['mediaLimit']);
        $this->setShowProfileInfo($config['showProfileInfo']);
        $this->setWrapHtml($config['wrapHtml']);
        $this->setWrapHtmlItems($config['wrapHtmlItems']);
        $this->setShowLikes($config['showLikes']);
        $this->setLikesLabel($config['likesLabel']);
        $this->setShowError($config['showError']);

    }

    /**
     * Generate and return an Instagram feed from the config values provided
     *
     * @return string
     *
     */
    public function generateHtmlFeed()
    {
        // Get JSON feed, returns null if bad response.
        $jsonFeed = $this->_getJsonFeed();  
        
        // Return early with an error message if appropriate.
        if (is_null($jsonFeed)) {
            return $this->_showError ? "<p class=\"instagram-feed-error\">Couldn't get a feed for username: $this->_username </p>" : '';
        }
        
        return $this->_convertToHtml($jsonFeed);        
    }
    
    /**
     * Convert the JSON feed to a HTML structure using the "wrapHtml" config options.
     *
     * @return string
     *
     */
    private function _convertToHtml($jsonFeed)
    {
        
        $htmlItemsArray = array();
        
        // Create & populate profile information html if required.
        if ($this->_showProfileInfo) {
            $buildItemArray = array();
            
            $imgPath = $jsonFeed['user']['profile_pic_url_hd'];
            $imgItem = "<img class=\"profile-picture\" src=\"$imgPath\">";
            $buildItemArray[] = $imgItem;
                        
            $usrUsername = $jsonFeed['user']['username'];
            $profileLink = "https://www.instagram.com/$usrUsername/";
            $usernameItem = "<p class=\"profile-username\"><a class=\"profile-username-link\" href=\"$profileLink\">$usrUsername</a></p>";
            $buildItemArray[] = $usernameItem;
            
            $postsCount = $jsonFeed['user']['media']['count'];
            $postItem = "<p class=\"profile-post-count\">$postsCount<span class=\"profile-post-count-label\">posts</span></p>";
            $followersCount = $jsonFeed['user']['followed_by']['count'];
            $followersItem = "<p class=\"profile-followers-count\">$followersCount<span class=\"profile-followers-label\">followers</span></p>";
            $followingCount = $jsonFeed['user']['follows']['count'];
            $followingItem = "<p class=\"profile-following-count\">$followingCount<span class=\"profile-following-label\">following</span></p>";

            $countItem = "<div class=\"profile-counts\">" . $postItem . $followersItem . $followingItem . "</div>";
            $buildItemArray[] = $countItem;            

            $usrName = $jsonFeed['user']['full_name'];
            $nameItem = "<p class=\"profile-name\">$usrName</p>";
            $buildItemArray[] = $nameItem;
            
            $usrBio = $jsonFeed['user']['biography'];
            $bioItem = "<p class=\"profile-biography\">$usrBio</p>";
            $buildItemArray[] = $bioItem;
            
            $profileWrapper = '<div class="profile-item">' . implode('', $buildItemArray) . '</div>';
            $htmlItem = $this->_wrapHtmlItems['start'] . $profileWrapper . $this->_wrapHtmlItems['end'];
            $htmlItemsArray[] = $htmlItem;
        }
        
        
        // Return here if the account is private, with error message if appropriate.
        if ($jsonFeed['user']['is_private']) {
            
            $errItem = "<p class=\"profile-private\">This Account is Private</p>";
            $htmlItem = $this->_wrapHtmlItems['start'] . $errItem . $this->_wrapHtmlItems['end'];  
            $htmlItemsArray[] = $htmlItem;

            return $htmlOutput = $this->_wrapHtml['start'] . implode('', $htmlItemsArray) . $this->_wrapHtml['end'];
                
        }

        // Create & populate media information depending on the config settings.
        $mediaItemsCount = 0;
        foreach ($jsonFeed['user']['media']['nodes'] as $mediaNode) {
            
            if ($mediaItemsCount < $this->_mediaLimit){
                
                $buildItemArray = array();
                $imgLink = "https://www.instagram.com/p/" . $mediaNode['code'];
                $linkItem = "<a class=\"media-link\" href=\"$imgLink\" target=\"_blank\"></a>";
                $buildItemArray[] = $linkItem;
                
                $imgPath = $mediaNode['thumbnail_src'];
                $imgItem = "<img class=\"media-image\" src=\"$imgPath\">";
                $buildItemArray[] = $imgItem;
                
                if ($this->_showLikes){
                    $likes = $mediaNode['likes']['count'];
                    $likesItem = "<p class=\"media-likes\"> $likes $this->_likesLabel</p>";
                    $buildItemArray[] = $likesItem;
                }
                
                $mediaWrapper = '<div class="media-item">' . implode('', $buildItemArray) . '</div>';
                $htmlItem = $this->_wrapHtmlItems['start'] . $mediaWrapper . $this->_wrapHtmlItems['end'];
                $htmlItemsArray[] = $htmlItem;
                $mediaItemsCount++;
            }
        }
        
        $htmlOutput = $this->_wrapHtml['start'] . implode('', $htmlItemsArray) . $this->_wrapHtml['end']; 
        return $htmlOutput;
    }

    /**
     * Retrieve JSON feed from Instagram.
     *
     * @return array
     *
     */
    private function _getJsonFeed()
    {
        
        // Create our feed URL using constants + provided username.
        $feed_url = self::FEED_URL . $this->_username . self::QUERY_STRING;
        
        // Get the feed and parse to JSON before returning.
        $rawReturn = @file_get_contents($feed_url);
        
        if (strpos($http_response_header[0], "200")) { 
           return json_decode($rawReturn, true);
        }
        
        // Return null if we couldn't get a feed and we can handle that higher up.
        return null;
        
    }
    

   /**
     * Setters / Getters.
     * Allow for changes to config without re-instantiating class.
     *
     */
    
    public function setUsername($username){
        $this->_username = $username;
    }
    
    public function getUsername(){
        return $this->_username;
    }

    public function setMediaLimit($mediaLimit){
        $this->_mediaLimit = $mediaLimit;
    }
    
    public function getMediaLimit(){
        return $this->_mediaLimit;
    }
    
    public function setShowProfileInfo($showProfileInfo){
        $this->_showProfileInfo = $showProfileInfo;
    }
    
    public function getShowProfileInfo(){
        return $this->_showProfileInfo;
    }
    
    public function setWrapHtml($wrapHtml){
        $this->_wrapHtml = $wrapHtml;
    }
    
    public function getWrapHtml(){
        return $this->_wrapHtml;
    }
        
    public function setWrapHtmlItems($wrapHtmlItems){
        $this->_wrapHtmlItems = $wrapHtmlItems;
    }
    
    public function getWrapHtmlItems(){
        return $this->_wrapHtmlItems;
    }
    
    public function setShowLikes($showLikes){
        $this->_showLikes = $showLikes;
    }
    
    public function getShowLikes(){
        return $this->_showLikes;
    }
    
    public function setLikesLabel($likesLabel){
        $this->_likesLabel = $likesLabel;
    }
    
    public function getLikesLabel(){
        return $this->_likesLabel;
    }
    
    public function setShowError($showError){
        $this->_showError = $showError;
    }
    
    public function getShowError(){
        return $this->_showError;
    }
}

?>
