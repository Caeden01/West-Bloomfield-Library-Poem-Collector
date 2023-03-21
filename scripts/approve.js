// Autogrow the size of the textbox to match the space of the poems.
var auto_grow = function(b) {
  b.style.height = "5px";
  b.style.height = b.scrollHeight + "px";
}
// Submit an XHTML request to verfiy login information and to obtain database info.
var login = function() {
  var b = new XMLHttpRequest();
  b.open("POST", "./approve.php", !0);
  b.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  b.onload = function() {
    if ("wrong" == this.responseText) {
      // If the login is incorrect, inform the user.
      alert("Your login is incorrect");
    } else {
      // If the login is correct, load the database info.
      document.querySelector(".login_container").classList.add("animate__bounceOut");
      document.querySelector(".approve_container").innerHTML = this.responseText;
      // For each approve button, create an onclick event.
      for (var e = document.querySelectorAll(".approve_btn"), c = 0; c < e.length; c++) {
        e[c].onclick = function() {
          var a = "true", d = "";
          // When the approve button is selected, check to see if the poem has been edited.
          document.getElementById(this.id).value == document.getElementById(this.id + "org").value ? a = "false" : d = document.getElementById(this.id + "org").value;
          var f = new XMLHttpRequest();
          f.open("POST", "./approve.php", !0);
          f.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          f.onload = function() {
            // If there is an issue with approving a poem, notify the user.
            "success" != this.responseText && alert("Error: could not approve poem.");
          };
          // Submit an xHTML request to update the database to approve the poem.
          f.send("username=" + encodeURI(document.getElementById("name").value) + "&password=" + encodeURI(document.getElementById("password").value) + "&approve=" + this.id + "&content=" + encodeURI(document.getElementById(this.id).value) + "&edited=" + a + "&org=" + d);
          // Hide the poem from the table listing.
          this.parentElement.parentElement.remove();
        };
      }
      // Create an array of all discard buttons.
      e = document.querySelectorAll(".delete_btn");
      for (c = 0; c < e.length; c++) {
        // Create an onclick event for each discard button in the table.
        e[c].onclick = function() {
          var a = new XMLHttpRequest();
          a.open("POST", "./approve.php", !0);
          a.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          a.onload = function() {
            // If there is an issue with discarding a poem, notify the user.
            "success" != this.responseText && alert("error");
          };
          a.send("username=" + encodeURI(document.getElementById("name").value) + "&password=" + encodeURI(document.getElementById("password").value) + "&remove=" + this.id);
          // Hide the poem from the table listing.
          this.parentElement.parentElement.remove();
        };
      }
      setTimeout(function() {
        document.querySelector(".login_container").style.display = "none";
        document.querySelector(".approve_container").style.display = "block";
        document.querySelector(".approve_container").classList.add("animate__bounceIn");
        // Adjust the size of each textbox to the space the text takes up.
        for (var a = document.querySelectorAll("textarea"), d = 0; d < a.length; d++) {
          auto_grow(a[d]);
        }
      }, 700);
    }
  };
  // Send an xHTML request to verfiy the login info to the site.
  b.send("username=" + encodeURI(document.getElementById("name").value) + "&password=" + encodeURI(document.getElementById("password").value));
}
// When the login button is clicked, login to the website.
document.querySelector(".login").onclick = function() {
  login();
};
// When enter is tapped on the keyboard, login to the website.
var inputs = document.querySelectorAll("input");
for(var i = 0; i < inputs.length; i++) {
  inputs[i].onkeydown = function(e) {
    if(e.which == 13) login();
  }
}
