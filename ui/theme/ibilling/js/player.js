localStorage.removeItem("is_tvs_loaded");
localStorage.setItem("search_type", "");
localStorage.setItem("type_list_blocks", 0);
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
var player;
function onYouTubeIframeAPIReady() {
  player = new YT.Player('player', {
    height: '50%',
    width: '40%',
    videoId: 'E0vPbmwjT9Y',
    playerVars: {
        'autoplay': 0,
        'controls': 0,
        'showinfo': 0,
        'rel': 0
    },
    events: {
      'onReady': onPlayerReady,
      'onStateChange': onPlayerStateChange,
    }
  });
}

function onPlayerReady(event) {
  event.target.playVideo();
  player.mute();
}

var done = false;
function onPlayerStateChange(event) {
  if (event.data == YT.PlayerState.PLAYING && !done) 
    done = true;
  
  if (event.data == YT.PlayerState.ENDED) 
    player.playVideo();    
}