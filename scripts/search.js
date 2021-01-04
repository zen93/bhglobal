var type, query, options, selected = -1, livesearch, searchbox, originalquery;
var isRelationship = false, selectionresults, basecompanyid = null;

function hideLivesearch(){
  selected = -1;
  livesearch.innerHTML = "";
  livesearch.style.border = "0px";
}

var clickHandler = function () {
  searchbox.value = this.innerText;
}

function reg_click_handler() {
  results = livesearch.children;
  len = results.length;
  for(i=0;i<len;i++) {
    results[i].onmousedown = clickHandler;
  }
}


function stopEvent(event) {
  if(event.keyCode == 40 || event.keyCode == 38 || event.keyCode == 13) {
    event.preventDefault();
    return false;
  }
}

function keyHandler(event) {
  resultViews = livesearch.getElementsByClassName('liveresults');
  //console.log(resultViews);
  if(!searchbox === document.activeElement) {
    // console.log("Not active!");
    return false;
  }
  if(event.keyCode == 27) {
    //escape
    //searchbox.value = originalquery;
    hideLivesearch();
    event.preventDefault();
    return false;
  }
  if(event.keyCode == 40) {
    //downarrow
    if(resultViews != null && selected < (resultViews.length - 1)) {
      if(selected == -1)
        originalquery = searchbox.value;
      selected++;
      resultViews[selected].style.background = "#f1f1f1";
      if(selected > 0)
        resultViews[selected-1].style.background = "white";
      searchbox.value = resultViews[selected].innerText;
    }
    event.preventDefault();
    return false;
  }
  if(event.keyCode == 38) {
    //uparrow
    if(resultViews != null && selected > -1) {
      resultViews[selected].style.background = "white";
      selected--;
      if(selected != -1) {
        resultViews[selected].style.background = "#f1f1f1";
        searchbox.value = resultViews[selected].innerText;
      }
      if(selected == -1) {
        searchbox.value = originalquery;
        return;
      }
    }
    event.preventDefault();
    return false;
  }
  if(event.keyCode == 13) {
    //Enter key
    if(selected != -1) {
      searchbox.value = resultViews[selected].innerText;
      hideLivesearch();
    }
    else {
      if(query != "") {
        search_selection_query();
      }
      else {
        search_query();
      }
    }
    event.preventDefault();
    return false;
  }
  else {
    search();
  }
}

function getResult(type, query, options="3", callback) {
  if(query.length == 0) {
    hideLivesearch();
    return;
  }
  if(window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function () {
      if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        response = xmlhttp.responseText;
        if(response != "") {
          var data = JSON.parse(response);
          livesearch.innerHTML = "";
          selected = -1;
          for(i=0;i<data["results"].length;i++) {
              livesearch.innerHTML += String("<span class='liveresults'>" + data["results"][i].name + "</span>");
          }
          callback();
        }
        else{
          livesearch.innerHTML = "No result";
        }
        livesearch.style.border = "1px solid black";
        livesearch.style.borderTop = "0px";
      }
    }
    xmlhttp.open("GET", "ajaxsearch.php?" + type + "=" + encodeURIComponent(query) + "&options=" + options);
    xmlhttp.send();
  }
  else {
    livesearch.innerHTML = "Sorry, your browser does not support this feature.";
  }
}

function init() {
  searchbox = document.getElementById('searchbox');
  searchbutton = document.getElementById('searchbutton');
  livesearch = document.getElementById('livesearch');
  searchTypes = document.getElementsByName('searchtype');

  if(searchbox == null || searchbutton == null || livesearch == null || searchTypes == null)
    return;

  if(searchbox.name == "relationship") {
    isRelationship = true;
    selectionresults = document.getElementById('selectionresults');
    basecompanyid = document.getElementById('basecompanyid').value;
    // console.log(isRelationship);
  }
  searchbox.placeholder = "Search..";
  searchbox.autocomplete = "off";
  searchbutton.value = "Search";

  searchbox.onblur = hideLivesearch;
  searchbox.onfocus = search;
  searchbox.onkeydown = stopEvent;
  searchbox.onkeyup = keyHandler;

  if(isRelationship)
    searchbutton.onclick = search_selection_query;
  else
    searchbutton.onclick = search_query;
}

function load_values() {
  for(i=0;i<searchTypes.length;i++) {
    if(searchTypes[i].checked) {
      type = searchTypes[i].value;
    }
  }
  options = "3";
  query = document.getElementById('searchbox').value;
  if(query.length > 0)
    query = query;
}

function search_query() {
  load_values();
  if(query.length==0)
    return;
  window.location.href="search.php?" + type + "=" + encodeURIComponent(query);
}

function search_selection_query() {
  load_values();
  if(query.length == 0) {
    return;
  }
  if(window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function () {
      if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        response = xmlhttp.responseText;
        if(response != "" || response != "No results") {
          var el = document.createElement('html');
          el.innerHTML = response;
          var resultstable = el.getElementsByTagName('table')[0];
          if(resultstable) {
            selectionresults.innerHTML = "";
            selectionresults.appendChild(resultstable);
            selectionresults.style="margin-top: 10px";
          }
          else {
            selectionresults.innerHTML = "No results";
          }
        }
        else{
          selectionresults.innerHTML = "No result";
        }
      }
    }
    xmlhttp.open("GET", "search.php?" + type + "=" + encodeURIComponent(query) + "&selection=" + basecompanyid + "&options=" + options);
    xmlhttp.send();
  }
  else {
    selectionresults.innerHTML = "Sorry, your browser does not support this feature.";
  }
}


function search() {
  //var type = "";
  load_values();
  getResult(type, query, options, reg_click_handler);

}
window.onload = init;
