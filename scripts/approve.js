// Autogrow the size of the textbox to match the space of the poems.
function auto_grow(element) {
  element.style.height = "5px";
  element.style.height = element.scrollHeight + "px";
}

// Submit an XHTML request to verfiy login information and to obtain database info.
function login() {
  const login_request = new XMLHttpRequest();

  login_request.open("POST", "./approve.php", !0);
  login_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  login_request.onload = () => {
    if ("wrong" == login_request.responseText) {
      // If the login is incorrect, inform the user.
      alert("Your login is incorrect");
    } else {
      // If the login is correct, load the database info.
      document.querySelector(".login_container").classList.add("animate__bounceOut");
      document.querySelector(".approve_container").innerHTML = login_request.responseText;

      // For each approve button, create an onclick event.
      const approve_buttons = document.querySelectorAll(".approve_btn");
      for (let i = 0; i < approve_buttons.length; ++i) {
        const approve_button = approve_buttons[i];
        approve_button.onclick = () => {
          let edited = true, org = "";
          // When the approve button is selected, check to see if the poem has been edited.
          if (document.getElementById(login_request.id).value == document.getElementById(login_request.id + "org").value) edited = false;
          else org = document.getElementById(approve_button.id + "org").value;

          const approve_request = new XMLHttpRequest();
          approve_request.open("POST", "./approve.php", !0);
          approve_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          approve_request.onload = () => {
            // If there is an issue with approving a poem, notify the user.
            "success" != approve_request.responseText && alert("Error: could not approve poem.");
          };

          // Submit an xHTML request to update the database to approve the poem.
          approve_request.send(
            "username=" + encodeURI(document.getElementById("name").value) +
              "&password=" + encodeURI(document.getElementById("password").value) +
              "&approve=" + approve_button.id +
              "&content=" + encodeURI(document.getElementById(approve_button.id).value) +
              "&edited=" + edited +
              "&org=" + org
          );
          // Hide the poem from the table listing.
          approve_button.parentElement.parentElement.remove();
        };
      }

      // Create an array of all discard buttons.
      const delete_buttons = document.querySelectorAll(".delete_btn");
      for (let i = 0; i < delete_buttons.length; ++i) {
        // Create an onclick event for each discard button in the table.
        const delete_button = delete_button[i]
        delete_button.onclick = () => {
          const approve_request = new XMLHttpRequest();
          approve_request.open("POST", "./approve.php", !0);
          approve_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          approve_request.onload = () => {
            // If there is an issue with discarding a poem, notify the user.
            "success" != delete_button.responseText && alert("error");
          };
          approve_request.send(
            "username=" + encodeURI(document.getElementById("name").value) +
              "&password=" + encodeURI(document.getElementById("password").value) +
              "&remove=" + delete_button.id
          );
          // Hide the poem from the table listing.
          delete_button.parentElement.parentElement.remove();
        };
      }

      setTimeout(() => {
        document.querySelector(".login_container").style.display = "none";
        document.querySelector(".approve_container").style.display = "block";
        document.querySelector(".approve_container").classList.add("animate__bounceIn");
        // Adjust the size of each textbox to the space the text takes up.
        const text_areas = document.querySelectorAll("textarea");
        for (let i = 0; i < text_areas.length; ++i) {
          auto_grow(text_areas[i]);
        }
      }, 700);
    }
  };

  // Send an xHTML request to verfiy the login info to the site.
  login_request.send(
    "username=" + encodeURI(document.getElementById("name").value) +
      "&password=" + encodeURI(document.getElementById("password").value)
  );
}

// When the login button is clicked, login to the website.
document.querySelector(".login").onclick = () => login();

// When enter is tapped on the keyboard, login to the website.
const inputs = document.querySelectorAll("input");
for (let i = 0; i < inputs.length; i++) {
  inputs[i].onkeydown = (e) => {
    if(e.which === 13) login();
  }
}
