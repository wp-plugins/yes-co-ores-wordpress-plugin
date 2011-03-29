/**
 * Quick gathering of junk files for the slider
 */

var ScrollTimer;
var popupTimer      = 4000;
var thumbsWidth     = 0;
var isScrolling     = false;
var slideEvent      = false;
var slideshowTimer  = false;

function setPopupImage(div, imgId, img, number)
{
  console.log('Setting current popup image [', div, ']');
  if(document.getElementById(div))
  {
    blendimage(div, imgId, img, 500)
  }

  setCurrentNav(number);
  updateNavBar(number);
}

function getNavigationItemsWidth()
{
  thumbsWidth     = 0;
  var thumbsElem  = document.getElementById('thumbs');

  if(thumbsElem)
  {
    var nav = thumbsElem.childNodes
    if (nav)
    {
      for(i=0;i<nav.length;i++)
      {
        if(nav[i].className=='navigation')
        {
          thumbsWidth += nav[i].offsetWidth;
        }
      }
    }

    thumbsElem.style.width = thumbsWidth + 'px';
  }
}

function setCurrentNav(number)
{
  var thumbsHolder = document.getElementById('thumbs');
  if(thumbsHolder)
  {
    divs = thumbsHolder.getElementsByTagName('div');

    for (var i=0;i<divs.length;i++)
    {
      if(divs[i].id=='nav'+number)
      {
        divs[i].className     = 'item current';
      }
      else if(divs[i].className=='item current')
      {
        divs[i].className     = 'item';
      }
    }

    //ie6 hack.
    wait(250);
  }
}

function cancelSlideNavigation()
{
  if(slideEvent)
  {
    clearTimeout(slideEvent);
    slideEvent = false;
  }
}

function slideNavigationHorizontal(elemId, pos)
{
  var elem = document.getElementById(elemId);
  if(elem)
  {
    cancelSlideNavigation();
    var currentScroll = elem.scrollTop;

    if(currentScroll == pos || (currentScroll-1)==pos || (currentScroll+1)==pos)
    {
      elem.scrollTop = pos;
    }
    else
    {
      var oldScroll = elem.scrollTop;
      if(currentScroll > pos)
      {
        elem.scrollTop -= 2;
      }
      else if(currentScroll < pos)
      {
        elem.scrollTop += 2;
      }

      if (oldScroll != elem.scrollTop)
        slideEvent = setTimeout( "slideNavigationHorizontal('" + elemId + "'," + pos + ")", 1);
    }
  }
}

function slideNavigationVertical(elemId, pos)
{
  var elem = document.getElementById(elemId);
  if(elem)
  {
    cancelSlideNavigation();
    var currentScroll = elem.scrollLeft;

    if(((pos+2) > currentScroll) && ((pos-2) < currentScroll))
    {
      elem.scrollLeft = pos;
    }
    else
    {
      var oldScroll = elem.scrollLeft;
      if(currentScroll > pos)
      {
        elem.scrollLeft -= 2;
      }
      else if(currentScroll < pos)
      {
        elem.scrollLeft += 2;
      }

      if (oldScroll != elem.scrollLeft)
        slideEvent = setTimeout( "slideNavigationVertical('" + elemId + "'," + pos + ")", 1);
    }
  }
}

function scrollToActiveThumbnail()
{
  var thumbsHolder = document.getElementById('thumbs');
  if(thumbsHolder)
  {
    divs    = thumbsHolder.getElementsByTagName('div');
    number  = 1;
    for (var i=0;i<divs.length;i++)
    {
      if(divs[i].className == 'item')
      {
        number++;
      }
      else if(divs[i].className=='item current')
      {
        updateNavBar(number);
        break;
      }
    }
  }
}

function updateNavBar(number)
{
  var thumbsHolder  = document.getElementById('thumbsHolder');
  var thumbs        = document.getElementById('thumbs');

  if (thumbsHolder && thumbs)
  {
    var thumbsHolderWidth   = thumbsHolder.offsetWidth;
    var thumbsHolderHeight  = thumbsHolder.offsetHeight;

    if(thumbsHolderHeight > thumbsHolderWidth)
    {
      updateNavBarHorizontal(number, thumbsHolder, thumbs);
    }
    else
    {
      updateNavBarVertical(number, thumbsHolder, thumbs);
    }
  }
}

function updateNavBarHorizontal(number, thumbsHolder, thumbs)
{
  var thumbsHeight        = parseInt(thumbs.offsetHeight);
  var thumbsHolderHeight  = parseInt(thumbsHolder.offsetHeight);
  var thumbsOffset        = 0;
  var itemWidth           = 0;
  var counter             = 0;

  if (thumbsHeight > thumbsHolderHeight)
  {
    if (thumbs.hasChildNodes())
    {
      for (i = 0; i < thumbs.childNodes.length; i++)
      {
        if (thumbs.childNodes[i].className == 'navigation')
        {
          if(counter == number)
          {
            break;
          }
          counter++;
          itemWidth = thumbs.childNodes[i].offsetHeight
          thumbsOffset += itemWidth;
        }
      }
    }
    slideNavigationHorizontal(thumbsHolder.id, (thumbsOffset + (itemWidth / 2)) - (thumbsHolderHeight /2) );
  }
}

function updateNavBarVertical(number, thumbsHolder, thumbs)
{
  var thumbsHolderWidth = thumbsHolder.offsetWidth;

  if (thumbsWidth == 0) {
    thumbsWidth = parseInt(thumbs.style.width);
  }

  if (thumbsWidth > thumbsHolderWidth)
  {
    var currentPos = 0;
    var currentItemWidth = 0;

    if (thumbs.hasChildNodes())
    {
      for (i = 0; i < thumbs.childNodes.length; i++)
      {
        if (thumbs.childNodes[i].className == 'navigation')
        {

          var subNav = thumbs.childNodes[i].childNodes;
          for (j = 0; j < subNav.length; j++) {
            if (subNav[j].id == 'nav'+number) {
              currentItemWidth = thumbs.childNodes[i].offsetWidth;
              break;
            }
          }

          if (currentItemWidth > 0) {
            break;
          }

          currentPos += thumbs.childNodes[i].offsetWidth;
        }
      }
    }

    slideNavigationVertical(thumbsHolder.id, currentPos - ((thumbsHolderWidth - currentItemWidth) / 2));
  }
}

function dowloadImage(uuid, type)
{
  var base  = getBaseHref();
  var url   = '';


  if (typeof(base) != 'undefined' && base != null)
  {
    url += base.href;
    if (url.substr(-1) != '/')
    {
      url += '/';
    }
    url += 'frontend-g4/tools/';
  }

  var divs = document.getElementById('thumbs');
  divs = divs.getElementsByTagName('div');

  for (var i=0;i<divs.length;i++)
  {
    if(divs[i].className=='item current' && divs[i].id)
    {
      var number = divs[i].id.replace("nav","");
      url += 'image_save.php?uuid=' + uuid + '&number=' + number + '&uri=' + obtainURI();
      if (type)
        url += '&type=' + type;

      document.location = url;
      break;
    }
  }
}

function downloadFloorplan(uuid)
{
  dowloadImage(uuid, 'floorplan');
}

function moveNext()
{
  var divs = document.getElementById('thumbs');
  if(divs)
  {
    divs            = divs.getElementsByTagName('div');
    var done        = false;
    var next        = false;
    var nextItem    = false;
    var counter     = 0;

    for (var i=0;i<divs.length;i++)
    {
      if((divs[i].className=='item current' || divs[i].className=='item') && divs[i].id != '')
      {
        if(next==true)
        {
          nextItem = divs[i];
          break;
        }

        if(divs[i].className=='item current')
        {
          next = true;
        }
        counter++;
      }
    }

    if(nextItem)
    {
      nextItem.onclick();
      done = true;
    }

    return done;
  }
}

function movePrev()
{
  var divs = document.getElementById('thumbs');
  if(divs)
  {
    var divs = divs.getElementsByTagName('div');
    var last = false;

    for (var i=0;i<divs.length;i++)
    {
      if((divs[i].className=='item current' || divs[i].className=='item') && divs[i].id!='')
      {
        if(divs[i].className=='item current' && last)
        {
          last.onclick();
          break;
        }
        last = divs[i];
      }
    }
  }
}

function startSlideShow(time)
{
  if(time>1000)
  {
    popupTimer = time;
  }

  var slideshowBarOnElem  = document.getElementById('slideshowBarOn');
  var slideshowBarOffElem = document.getElementById('slideshowBarOff');

  if(slideshowBarOnElem && slideshowBarOffElem && (popupTimer>0))
  {
    slideshowBarOnElem.style.display  = 'none';
    slideshowBarOffElem.style.display = 'inline';
    if(moveNext())
    {
      slideshowTimer = setTimeout( "startSlideShow()", popupTimer);
    }
    else
    {
      slideshowBarOffElem.style.display = 'none';
      slideshowBarOnElem.style.display  = 'inline';
    }
  }
}

function stopSlideShow()
{
  var slideshowBarOnElem  = document.getElementById('slideshowBarOn');
  var slideshowBarOffElem = document.getElementById('slideshowBarOff');

  if(slideshowBarOnElem && slideshowBarOffElem)
  {
    slideshowBarOffElem.style.display = 'none';
    slideshowBarOnElem.style.display  = 'inline';
    if(slideshowTimer)
    {
      clearTimeout(slideshowTimer);
    }
    popupTimer = 0;
  }
}

function getRefToDivMod( divID, oDoc ) {
	if( !oDoc ) { oDoc = document; }
	if( document.layers ) {
		if( oDoc.layers[divID] ) { return oDoc.layers[divID]; } else {
			for( var x = 0, y; !y && x < oDoc.layers.length; x++ ) {
				y = getRefToDivNest(divID,oDoc.layers[x].document); }
			return y; } }
	if( document.getElementById ) { return oDoc.getElementById(divID); }
	if( document.all ) { return oDoc.all[divID]; }
	return oDoc[divID];
}

function resize()
{
  if(document.getElementById('popup'))
  {
    idOfDiv = 'popup';
	  var oH = getRefToDivMod( idOfDiv ); if( !oH ) { return false; }
	  var x = window; x.resizeTo( screen.availWidth, screen.availWidth );
	  var oW = oH.clip ? oH.clip.width : oH.offsetWidth;
	  var oH = oH.clip ? oH.clip.height : oH.offsetHeight; if( !oH ) { return false; }
	  x.resizeTo( oW + 200, oH + 200 );
	  var myW = 0, myH = 0, d = x.document.documentElement, b = x.document.body;
	  if( x.innerWidth ) { myW = x.innerWidth; myH = x.innerHeight; }
	  else if( d && d.clientWidth ) { myW = d.clientWidth; myH = d.clientHeight; }
	  else if( b && b.clientWidth ) { myW = b.clientWidth; myH = b.clientHeight; }
	  if( window.opera && !document.childNodes ) { myW += 16; }
	  //second sample, as the table may have resized
	  var oH2 = getRefToDivMod( idOfDiv );
	  var oW2 = oH2.clip ? oH2.clip.width : oH2.offsetWidth;
	  var oH2 = oH2.clip ? oH2.clip.height : oH2.offsetHeight;
	  x.resizeTo( oW2 + ( ( oW + 200 ) - myW ), oH2 + ( (oH + 200 ) - myH ) );
  }
}

function ScrollLeft(id)
{
  ScrollTimer = setInterval("document.getElementById('" + id + "').scrollLeft -= 2", 15);
}

function ScrollRight(id)
{
  ScrollTimer = setInterval("document.getElementById('" + id + "').scrollLeft += 2", 15);
}

function ScrollUp(id)
{
  ScrollTimer = setInterval("document.getElementById('" + id + "').scrollTop -= 2", 15);
}

function ScrollDown(id)
{
  ScrollTimer = setInterval("document.getElementById('" + id + "').scrollTop += 2", 15);
}

function stopScroll()
{
  clearInterval(ScrollTimer);
}


function thumbsWidthDetails()
{
  setObjectsThumbsWidth('scrollerThumbs','thumbnail');
}

function setObjectsThumbsWidth(div,classe)
{
  alert('hier');
  if(holder=document.getElementById(div))
  {
    var thumbsWidth = 0;
    count = 0;
    if(nav=document.getElementsByTagName('div'))
    {
      for(i=0;i<nav.length;i++)
      {
        if(nav[i].className.indexOf(classe)!=-1)
        {
          thumbsWidth += nav[i].offsetWidth;
          count++;
        }
      }
    }
    holder.style.width = (thumbsWidth + 4) + 'px';
  }
}

function addThumbnails(images, enableSliding)
{
  images            = dojo.fromJson(images);

  console.log('Calling: place thumbnails');

  if(container = document.getElementById('slider-container'))
  {
    for(var i=0; i<images.length; i++)
    {
      image = images[i].split('<>');

      var img = document.createElement("img");
      container.appendChild(img);

      img.src     = image[1];
      img.org_img = image[0];
      
      if (enableSliding == true)
      {
        img.number  = i;
        img.id      = 'image' + i;
        img.onclick = function() {moveSpecificImage(this.number);};
      }
      else
      {
        img.onclick = function() {changePhoto(this.org_img);};
      }
    }
    activeImage = 0;
  }

  // Show the image slider
  var imageSliderHolder = dojo.byId('imgsliderholder');

  if (imageSliderHolder)
    dojo.style(imageSliderHolder, 'display', 'block');

}

function changePhoto(src)
{
  if(img = document.getElementById('bigImage'))
  {
    img.src = src;

    console.log(src);
  }
}

/**
* Move to a specific image
*
* @param int imageNumber
* @return void
*/
function moveSpecificImage(imageNumber)
{
  console.log('Moving to image number [', imageNumber, ']');

  if ((firstImage = document.getElementById('image0')) && (image = document.getElementById('image' + imageNumber)) && (holder = document.getElementById('imgslider')))
  {
    var pos = (image.offsetLeft - firstImage.offsetLeft) - (holder.offsetWidth  / 2) + (image.offsetWidth / 2);
    if (pos < 0)
      pos = 0;

    slideNavigationVertical(holder.id, pos);
    changePhoto(image.org_img);
    activeImage = imageNumber;
  }
}
