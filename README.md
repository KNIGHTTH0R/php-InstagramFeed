# php-InstagramFeed


Generate an Instagram post feed without an API key.

No requirements, no messing around with the Instagram API, just a straightforward HTML Instagram feed pulled live from any public Instgram account.

Suitable for use with Bootstrap. Each returned "item" can be wrapped with user specified HTML tags allowing for row/column layouts.

Since this class does **not** interact with the official Instagram API, it cannot display posts for accounts that are set to private. 

You can find a live example and some basic styling over at my blog: 

http://benjaminhughes.co.uk/blog/web/creating-instagram-feed-for-website/

	
    
<img src="http://benjaminhughes.co.uk/wp-content/uploads/2017/08/instagramfeed.png" width="100%">

---

### Example Usage


```html
<?php
include_once('InstagramFeed.php');


// Build options array and wrap returned items in Bootstrap row/col classes.
$options = array(
	'mediaLimit' => 1,
    'wrapHtml' => array(
        'start' => '<div class="row instagram-feed">',
        'end' => '</div>',
    ),
    'wrapHtmlItems' => array(
        'start' => '<div class="col-xs-12 col-sm-6 col-md-3 feed-item">',
        'end' => '</div>',
    )
);

$igFeed = new InstagramFeed('username', $options);
?>

<html>
  <body>
  	<!-- Output Instagram Feed  -->
  	<?php echo $igFeed->generateHtmlFeed(); ?>
  </body>
</html>

```

### Example Output

*Links &amp; images removed for brevity* 

```html
<div class="row instagram-feed">
  <div class="col-xs-12 col-sm-6 col-md-3 feed-item">
    <div class="profile-item">
      <img class="profile-picture" src="...">
      <p class="profile-username">
        <a class="profile-username-link" href="...">benjihughes_</a>
      </p>
      <div class="profile-counts">
        <p class="profile-post-count">
          19
          <span class="profile-post-count-label">posts</span>
        </p>
        <p class="profile-followers-count">
          1504
          <span class="profile-followers-label">followers</span>
        </p>
        <p class="profile-following-count">
          475
          <span class="profile-following-label">following</span>
        </p>
      </div>
      <p class="profile-name">benji</p>
      <p class="profile-biography">twenty three</p>
    </div>
  </div>
  <div class="col-xs-12 col-sm-6 col-md-3 feed-item">
    <div class="media-item">
      <a class="media-link" href="..." target="_blank"></a>
      <img class="media-image" src="...">
      <p class="media-likes">93 likes</p>
    </div>
  </div>
</div>
```



### Customisation options

**mediaLimit** - (int) Amount of media items to return.

**showProfileInfo** - (boolean) Display profile information item.

**wrapHtml** - (array) Html to wrap the entire return with.

**wrapHtmlItems** - (array) Html to wrap each returned item with.

**showLikes** - (boolean) Display likes for each media item.

**likesLabel** - (string) Label to show alongside the likes count.

**showError** - (boolean) Display an error message if feed fails.

The default values for each option can be seen in the array below.
```php
array(
  'mediaLimit' => 3,
  'showProfileInfo' => true
  'wrapHtml' => array(
  	'start' => '<div>',
    'end' => '</div>'
   ),       
  'wrapHtmlItems' => array(
  	'start' => '<div>',
    'end' => '</div>'
   ),       
  'showLikes' => true,
  'likesLabel' => 'likes',
  'showError' => true,
)
```
