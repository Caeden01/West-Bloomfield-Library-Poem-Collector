// Define the max poem character length
const MAX_POEM_LENGTH = 5000;
// After the name and email form is submitted, hide the get_started_container and display the write_containr.
document.querySelector("form").onsubmit = (e) => {
  document.querySelector(".get_started_container").classList.add("animate__bounceOut");
  setTimeout(() => {
    document.querySelector(".get_started_container").style.display = "none";
    document.querySelector(".write_container").classList.add("animate__bounceIn");
    document.querySelector(".write_container").style.display = "block";
  }, 650);
  setTimeout(() => {
    document.querySelector(".write_container").classList.remove("animate__bounceIn");
  }, 1500);
  e.preventDefault();
  return false;
};
// When the poem is submitted, hide the write container window and display a confirmation.
document.querySelector(".submit_poem").onclick = () => {
  document.querySelector(".write_container").classList.add("animate__backOutRight");
  setTimeout(() => {
    document.querySelector(".write_container").style.display = "none";
  }, 1000);
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "./", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onload = () => {
    if(this.responseText != "error") {
      // If the poem is submitted sucessfully, display a success window.
      var first_name = document.getElementById("name").value.replace(/ .*/, "");
      document.querySelector(".poem_id").innerHTML = this.responseText;
      document.querySelector(".submit_name").innerHTML = first_name;
      document.querySelector(".success_container").style.display = "block";
      document.querySelector(".success_container").classList.add("animate__backInRight");
      document.querySelector(".display_textarea").value = document.querySelector(".textarea_poem").value;
    } else {
      // If there is an error submitting a poem, display an error and set the text of the page equal to the poem content.
      alert("Error submitting poem. Please try again later.");
      document.innerHTML = document.querySelector(".textarea_poem").value;
    }
  };
  // Submit an xHTML request with the name, email,and poem content to the server.
  xhr.send("name=" + encodeURI(document.getElementById("name").value) + "&email=" + encodeURI(document.getElementById("email").value) + "&content=" + encodeURI(document.querySelector(".textarea_poem").value));
};
// As text is entered into the poem textbox, update the characters remaining indicator.
document.querySelector("textarea").oninput = () => {
  document.querySelector(".text_limit").innerHTML = MAX_POEM_LENGTH - this.value.length + " characters remaining";
};
