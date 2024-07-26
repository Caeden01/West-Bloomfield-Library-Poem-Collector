// Define the max poem character length
const MAX_LINES = 100;
const write_container = document.querySelector(".write_container");
const get_started_container = document.getElementById("get_started_container");
const text_area_poem = document.querySelector(".textarea_poem");
const dead_drop_background = document.querySelector(".dead_drop_background");
const success_container = document.getElementById("success_container");
const text_area_clone = document.querySelector(".textarea_poem_clone");
const page_0 = document.querySelector(".page_0");
const page_1 = document.querySelector(".page_1");
const page_2 = document.querySelector(".page_2");
const page_3 = document.querySelector(".page_3");
const page_4 = document.querySelector(".page_4");
const save_poem_button = document.getElementById("save_poem_button");
var on_page_function_array = [];

function getFormattedDate(date) {
  const months = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
  ];

  const day = date.getDate();
  const year = date.getFullYear();
  const month = months[date.getMonth()];

  // Get the appropriate suffix for the day
  const daySuffix = (day) => {
      if (day > 3 && day < 21) return 'th';
      switch (day % 10) {
          case 1: return 'st';
          case 2: return 'nd';
          case 3: return 'rd';
          default: return 'th';
      }
  };

  // Format the date
  const formattedDate = `${month} ${day}${daySuffix(day)}, ${year}`;

  return formattedDate;
}
var queue_new_animation = async (element, animation, delay = 750) => {
  element.classList.add(animation);

  // Using a promise to simulate asynchronous behavior
  return new Promise(resolve => {
    setTimeout(() => {
      element.classList.remove(animation);
      resolve(true); // Resolve the promise with true after animation is removed
    }, delay);
  });
};
var timeout = async (ms) => {
  return new Promise(resolve => setTimeout(resolve, ms));
}
var hide = (element) => {
  element.style.display = "none";
}
var show = (element) => {
  element.style.display = "block";
}
var make_request = async(parameters, path="./") => {
  try {
    const response = await fetch(path, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams(parameters)
    });
    const responseData = await response.text();
    // If you're not logged in, logged out.
    if(responseData == "Error: you cannot access this service because you are not logged in.") {
      logout();
    }
    return {text: responseData, status: response.status};
  } catch (error) {
    console.error('There was a problem with the fetch operation:', error);
    display_error('Fatal Error: Unable to load site data', 500);
    return null;
  }
};
// After the name and email form is submitted, hide the get_started_container and display the write_containr.
document.getElementById("get_started_form").onsubmit = (e) => {
  e.preventDefault();

  queue_new_animation(get_started_container, "animate__bounceOut", 650).then(() => {
    hide(get_started_container);
    show(write_container);
    check_flex(write_container, page_0);
    queue_new_animation(write_container, "animate__bounceIn", 850);
  });

  const name = document.getElementById("name").value;
  const submitNames = document.querySelectorAll(".submit_name");
  for(var i = 0; i < submitNames.length; i++) {
    submitNames[i].innerHTML = name;
  }
  const dateInfo = getFormattedDate(new Date());
  document.querySelector(".submit_date").innerHTML = dateInfo;

  return false;
};

const contact_container = document.getElementById("contact_container");
const post_contact_success = document.getElementById("post_contact_success");
document.getElementById("contact_form").onsubmit = (e) => {
  e.preventDefault();

  grecaptcha.execute();

  return false;
};
var submitNewPoem = () => {
  document.querySelector(".title_input_box").value = "";
  text_area_poem.value = "";
  save_poem_button.disabled = false;
  save_poem_button.innerHTML = "Save 🖫";
  queue_new_animation(success_container, "animate__backOutRight", 1200).then(() => {
    hide(success_container);
  });
  show(write_container);
  show(text_area_clone);
  queue_new_animation(write_container, "animate__backInRight", 1200).then(() => {
    check_flex(write_container, page_0);
  });
}
var getSecurityInformation = () => {
  const browserInfo = {
    appVersion: navigator.appVersion,
    platform: navigator.platform,
    language: navigator.language,
    languages: navigator.languages,
    cookiesEnabled: navigator.cookieEnabled,
    javaEnabled: navigator.javaEnabled(),
    online: navigator.onLine,
    screen: {
        width: screen.width,
        height: screen.height,
        availWidth: screen.availWidth,
        availHeight: screen.availHeight,
        colorDepth: screen.colorDepth,
        pixelDepth: screen.pixelDepth
    },
    saved_poems: getCookieArray("poems") || "N/A",
    memory: navigator.deviceMemory || "N/A",
    timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
  };

  return JSON.stringify(browserInfo, null, 2);
}

async function onSubmit(token) {
  switch(current_window_id) {
    case 0:
      var request = await make_request({name: document.getElementById("name").value, email: document.getElementById("email").value, title: document.querySelector(".title_input_box").value, poem: text_area_poem.value, token: token, securityInformation: getSecurityInformation()}, "./commands/addPoem.php");

      if(request.status == 201) {
        page_0.style.alignItems = 'center';
        page_0.scrollTo(0, 0);
        // If the poem is submitted sucessfully, display a success window.
        document.getElementById("poem_id").innerHTML = request.text.substring(10);
        show(success_container);
        hide(text_area_clone);
        queue_new_animation(write_container, "animate__backOutRight", 1200).then(() => {
          hide(write_container);
        });
        queue_new_animation(success_container, "animate__backInRight", 1200);
        save_poem();
      } else {
        display_error("We unfortunately couldn't submit your poem 😔. Either there is an issue currently with our servers or your internet has been connected. I recommend checking your wifi connection or trying to submit your poem later.");
      };
    break;
    case 4:
      const name = document.getElementById("contact_name").value;
      const email = document.getElementById("contact_email").value;
      const issue = document.getElementById("contact_issue").value;
      const response = document.getElementById("contact_response").value;
    
      document.getElementById("contact_submit_name").innerHTML = name;
    
      var request = await make_request({name: name, email: email, subject: issue, issue: response, securityInformation: getSecurityInformation(), token: token}, "./commands/addIssue.php");
      var response_array = JSON.parse(request.text);
      if(request.status != 201) {
        display_error("Error: Could not submit.");
        return;
      }
      document.getElementById("ticket_id").innerHTML = response_array.id;
      queue_new_animation(contact_container, "animate__bounceOut", 650).then(() => {
        hide(contact_container);
        show(post_contact_success);
        check_flex(post_contact_success, page_4);
        queue_new_animation(post_contact_success, "animate__bounceIn", 850);
      });
      break;
  }
  grecaptcha.reset();
};
// When the poem is submitted, hide the write container window and display a confirmation.
document.querySelector(".submit_poem").onclick = () => {
  const num_lines = get_height_and_number_of_lines().lines;
  if(num_lines > MAX_LINES) {
    display_error("Please submit only a maximum of "+ MAX_LINES +" lines! Too many lines means too much paper! Please cut down your poem!", 450);
  } else if(text_area_poem.value == "") {
    display_error("Please write something! What's the point in only submitting a blank wall of text?", 350);
  } else if(!document.querySelector(".title_input_box").value.length) {
    display_error("Please title your poem something!", 350);
  } else {
    grecaptcha.execute();
  }
};
// Content is set to zoom out 20% after the screen with is less than 430.
// Chrome doesn't recgonize this change in scrollHeight for some reason so it's being manually adjusted here.
var multiplier = 1;
const checkMultiplier = () => multiplier = (window.innerWidth < 430) ? 0.80 : 1;
checkMultiplier();
window.onresize = () => {
  checkMultiplier();
}

// As text is entered into the poem textbox, update the characters remaining indicator.
var check_flex = (content, scroll_container) => {
    // Check if the content is taller than the container
  if (content.scrollHeight * multiplier > scroll_container.clientHeight - 40) {
      // Align to flex-start if content is too tall
      scroll_container.style.alignItems = 'flex-start';
      content.classList.add("no_border_radius");
      content.classList.remove("expandable");
      return true;
  } else {
      // Center the content if it's not too tall
      scroll_container.style.alignItems = 'center';
      content.classList.add("expandable");
      content.classList.remove("no_border_radius");
      return false;
  }
}
on_page_function_array[2] = () => check_flex(page_2.querySelector(".container"), page_2);
on_page_function_array[3] = () => check_flex(page_3.querySelector(".container"), page_3);
on_page_function_array[4] = () => check_flex(contact_container, page_4);

var get_height_and_number_of_lines = () => {
  text_area_clone.value = text_area_poem.value;
  var height = text_area_clone.scrollHeight;
  const numberOfLines = Math.floor(height / 24);
  return {lines: numberOfLines, height: height};
};
var update_line_count_indicator = () => {
  const text_limit = document.querySelector(".text_limit");
  var number_of_lines = get_height_and_number_of_lines().lines;
  if(number_of_lines > MAX_LINES) {
    text_limit.innerHTML = "<span style='color: #d65e2f'>" + number_of_lines + "</span>" + " out of "+MAX_LINES+" lines remaining";
  } else {
    text_limit.innerHTML = number_of_lines + " out of "+MAX_LINES+" lines remaining";
  }
};
update_line_count_indicator();
text_area_poem.oninput = (e) => {
  text_area_poem.style.height = Math.max(get_height_and_number_of_lines().height, 150) + "px";
  check_flex(write_container, page_0);
  update_line_count_indicator();
};
var close_buttons = document.querySelectorAll(".close");
for(var i = 0; i < close_buttons.length; i++) {
  close_buttons[i].onclick = function() {
    hide(this.parentElement.parentElement);
    hide(dead_drop_background);
  }
};
document.querySelector(".terms_and_conditions_button").onclick = () => {
  const terms_and_conditions_box = document.getElementById("terms_and_conditions");
  queue_new_animation(terms_and_conditions_box, "animate__bounceIn", 800);
  show(terms_and_conditions_box);
  dead_drop_background.style.display = "flex";
};
// UPDATE
// Cookies have been replaced to local storage for increased space capacity
// Functions are currently not replaced tho for compability reasons
// TO DO: Rename functions

// Function to set a cookie with no expiration
function setCookie(name, value) {
  localStorage.setItem(name, value);
};
// Function to get a cookie by name
var getCookie = (name) => {
  return localStorage.getItem(name);
};
var getCookieArray = (name) => {
  return JSON.parse(getCookie(name));
}
var convertToSpecialChars = (mystring) => {
  return mystring.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;");
}
var createHTMLFromComments = (comment_array, admin=false, id) => {
  var allHTML = "";
  for(var i = 0; i < comment_array.length; i++) {
    allHTML += `<div class='comment'>
                <p><b>${convertToSpecialChars(comment_array[i].name)}</b>${admin ? `<button onclick="delete_comment('${id}', '${i}', this)">Delete</button>` : ""}
                <span class='time'>(${convertToSpecialChars(comment_array[i].time)})</span>:
                </p><p>${convertToSpecialChars(comment_array[i].comment)}</p>
                </div>`;
  }
  return allHTML;
}
var replaceNewLineWithBr = (str) => {
  return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
}
var syntaxHighlight = (json) => {
  json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
      var cls = 'number';
      if (/^"/.test(match)) {
          if (/:$/.test(match)) {
              cls = 'key';
          } else {
              cls = 'string';
          }
      } else if (/true|false/.test(match)) {
          cls = 'boolean';
      } else if (/null/.test(match)) {
          cls = 'null';
      }
      return '<span class="' + cls + '">' + match + '</span>';
  });
}
var toggleSecurityInfo = (e) => {
  var securityInfoContainer = e.parentElement.parentElement.querySelector(".securityInfoContainer");
  if (securityInfoContainer.style.display === 'none' || securityInfoContainer.style.display === '') {
    securityInfoContainer.style.display = 'block';
  } else {
    securityInfoContainer.style.display = 'none';
  }
}
var queuePoem = (id, e) => {
  e.disabled = true;
  make_request({action: "add", poem_id: id}, "./commands/queueManager.php").then((response) => {
    e.disabled = false;
    var response_object = JSON.parse(response.text);
    display_success(`Successfully queued print. Place in queue is <a href='#print-queue' style="color: orange" onclick="document.querySelector('.warning').querySelector('.close').click()"><b>#${response_object.queue_number}</b></a>.`, 350);  
  });
  
};
var createHTMLFromArray = (array, admin=false) => {
  const id = array.id;
  const name = array.name;
  const title = array.title;
  const poem = array.poem;
  const status = array.status;
  const timestamp = array.timestamp;
  const comments = array.comments;
  const printHistory = array.printHistory;
  const email = array.email && array.email.trim() !== '' ? array.email : 'n/a';
  const encodedId = array.encodedId;

  var comment_box = "";
  if(comments.length != 0) {
    comment_box = `<p>Comments:</p><div>${createHTMLFromComments(comments, admin, admin ? array.id : "")}</div>`;
  }
  var print_history = ``;
  if(printHistory.length != 0) {
    print_history += "<p>Print History:</p><div class='comment'>";
    for(var i = 0; i < printHistory.length; i++) {
      print_history += `<div> Printed on ${printHistory[i].time}</div>`;
    }
    print_history += "</div>";
  }
  var poem_info = `<div>Status: <b>${status}</b></div>
                   <div>ID: ${id}</div>`;
  if(email != "n/a") poem_info += `<div>Email: ` + email + "</div>";

  var admin_info = "";
  if(admin) {
    const securityInformation = syntaxHighlight(JSON.stringify(array.securityInformation, undefined, 4));
    const commentSecurityInformation = syntaxHighlight(JSON.stringify(array.commentSecurityInformation, undefined, 4));
    const history = array.history && array.history.trim() !== '' ? array.history : 'n/a';
    admin_info += `<div class="action-buttons flexbox" style='margin: 10px 0;'><button class='wide green' onclick='queuePoem("${id}", this)'>Queue Print</button></div>`;
    admin_info += `<div class="action-buttons flexbox"><button class='wide' onclick='toggleSecurityInfo(this)'>View Security Information</button></div>`;
    admin_info += "<div class='securityInfoContainer' style='display: none;'>";
    admin_info += `<p>Poem Moving History</p>`;
    admin_info += `<div class="comment" style="overflow: auto"><pre>${history}</pre></div>`;
    admin_info += `<p>Author Security Information JSON</p>`;
    admin_info += `<div class="comment" style="overflow: auto"><pre>${securityInformation}</pre></div>`;
    admin_info += `<p>Comment Security Information JSON</p>`;
    admin_info += `<div class="comment" style="overflow: auto"><pre>${commentSecurityInformation}</pre></div>`;
    admin_info += "</div>";

    admin_info += `<div class="action-buttons flexbox">`;
    if(status != "Pending") admin_info += `<button onclick='movePoem("${id}", "Pending", this)'>Move to Pending</button>`;
    if(status != "Approved") admin_info += `<button onclick='movePoem("${id}", "Approved", this)'>Move to Approved</button>`;
    if(status != "Rejected") admin_info += `<button onclick='movePoem("${id}", "Rejected", this)'>Move to Rejected</button>`;
    if(status != "Deleted") admin_info += `<button class='delete' onclick='deletePoem("${id}", this)'>Delete</button>`;
    admin_info += "</div>";
  }
  var qr_code_info = "";
  if(encodedId != undefined) {
    var qr = new QRious({
      value: url_comment_path+encodedId,
      level: qr_code_quality,
      size: 125
    });
    qr_code_info = `<div class="flexbox">
						<a target="_blank" rel="noopener noreferrer" href="${url_comment_path + encodedId}"><img style='margin-right: 10px; width: 150px; height: 150px' src="${qr.toDataURL()}"></a>
						Do you like the poem you read? Why not leave a comment for the writer? Scan the QR code to the left.
					</div>`
  }
  return `<div class="write_container" style="position: relative;">
        <div class="flexbox">
            <img src="${library_logo_bw}" style="width: 150px; position: relative; margin-right: 10px;">
            <p>${library_name} Poem Printer Receipt!</p>
        </div>
        <div class="flexbox">
            <img src="${qr_code_path}" style="width: 125px; position: relative; left: 5px; margin-right: 35px;">
            <p>Send in your own poem! Scan the QR code to the left!</p>
        </div>
                        <div class="title_input_box">${convertToSpecialChars(title)}</div>
                        <div class="textarea_poem" placeholder="Type Poem Here...">${replaceNewLineWithBr(convertToSpecialChars(poem))}</div>
                        <div style="word-wrap: break-word;">
                          <p>Sent in by <span class="submit_name">${convertToSpecialChars(name)}</span> on <span class="submit_date">${timestamp}</span></p>
                        </div>
                        ${qr_code_info}
                        <hr/>
                        <div class='bottom_poem_half'>
                        <p>Information:</p>
                        <div class='comment'>
                        ${poem_info}
                        </div>
                          ${print_history}
                          ${comment_box}
                          ${admin_info}
                        </div>
                      </div>`;
};
var extractAttributes = (arrayOfObjects, attributes) => {
  // Create a new array to store the extracted attribute values
  let extractedValuesArray = [];

  // Iterate through each object in the input array
  arrayOfObjects.forEach(obj => {
    // Create an object to store the extracted attributes for the current object
    let extractedAttributes = {};

    // Iterate through each attribute requested
    attributes.forEach(attribute => {
      // Check if the object has the current attribute
      if (obj.hasOwnProperty(attribute)) {
        // Add the attribute and its value to the extractedAttributes object
        extractedAttributes[attribute] = obj[attribute];
      }
    });

    // Push the extractedAttributes object into the new array
    extractedValuesArray.push(extractedAttributes);
  });

  // Return the new array containing objects with the extracted attribute values
  return extractedValuesArray;
}
const poem_box = document.querySelector(".poem_box");
// Function to search through the JSON array
var searchJsonArray = (jsonArray, searchKey) => {
  return jsonArray.filter(obj => 
    obj && (
      (obj.id && obj.id.toLowerCase().includes(searchKey.toLowerCase())) ||
      (obj.title && obj.title.toLowerCase().includes(searchKey.toLowerCase()))
    )
  );
}
var poem_list_array = [];
var visuallyUpdateMyPoems = () => {
  var search_value = document.getElementById('searchMyPoems').value;
  var filteredListArray = searchJsonArray(poem_list_array, search_value);

  poem_box.innerHTML = "";

  if(filteredListArray.length != 0) {
    var all_html = "";
    for(var i = filteredListArray.length - 1; i >= 0; i--) {
      if(filteredListArray[i]) {
        all_html += createHTMLFromArray(filteredListArray[i]);
      }
    }
    poem_box.innerHTML = all_html;
    poem_box.style.display = "flex";
    poem_box.style.textAlign = "left";
  } else {
    poem_box.style.display = "block";
    poem_box.style.textAlign = "center";
    poem_box.innerHTML = "<p>Sorry no poems found.</p>"
  }
  check_flex(document.getElementById("my_poem_container"), page_1);
};
document.getElementById('searchMyPoems').onkeyup = () => {
  visuallyUpdateMyPoems();
}
var refreshPoems = () => {
  var poem_ids_json = getCookie("poems");
  if(!poem_ids_json) return;

  make_request({ids: poem_ids_json}, "./commands/fetchPoems.php").then((response) => {
    poem_list_array = JSON.parse(response.text);
    visuallyUpdateMyPoems();
    var length_of_poem_array = poem_list_array.filter(Boolean).length;

    if(length_of_poem_array > 0) {
      document.querySelector(".poem_display_msg").innerHTML = `You have ` + length_of_poem_array + ` poems saved under this device. You can view them here, <a href="#home"><span style="color: orange; cursor: pointer;"><b>send in</b></span></a> some more poems, or add a poem to this list below.`;
    } else {
      document.querySelector(".poem_display_msg").innerHTML = `<p>You currently have no poems saved. You can either <a href="#home"><span style="color: orange; cursor: pointer;"><b>send in</b></span></a> and save a poem or you can add one below.</p><p>Add one here:</p>`
    }
  });
};
on_page_function_array[1] = refreshPoems;

// Function to add an ID to the array stored in the cookie
var addIDToArrayInCookie = (cookieName, id) => {
  var idsArray = [];
  var existingCookie = getCookieArray(cookieName);

  idsArray = existingCookie || [];
  // Check if the ID already exists in the array
  if (idsArray.includes(id)) {
    // Optional: Handle case where ID is already in the array
    return false;
  }
  // If ID doesn't exist, push it to the array
  idsArray.push(id);
  setCookie(cookieName, JSON.stringify(idsArray));
  
  return true; // Return true to indicate successful addition
};
var save_poem = () => {
  var id_code = document.getElementById("poem_id").innerHTML;
  if(!addIDToArrayInCookie("poems", id_code)) {
    console.error("Critical Error: Poem ID not saved");
  }
  if(typeof localStorage == 'undefined') {
    console.error("Can't save poem to browser. Local storage is disabled. Check if you're logged into incognito and make sure to write your ID code down somewhere.", 400);
  }
};
// This prevents ppl from accidentally forgetting to save their poems and losing them
var fake_save_poem = (obj) => {
  obj.disabled = true;
  obj.innerHTML = "Saved 🖫"; 
}
document.getElementById("add_button").onclick = function() {
  var id = document.querySelector(".id_code").value;
  this.disabled = true;
  make_request({id: id}, "./commands/checkPoemApprovalStatus.php").then((response) => {
    this.disabled = false;
    if(response.text == "false") {
      display_error("ID code does not exist. Please try retyping the code or trying different letters.", 300);
    } else if(response.text == "true") {
      if(!addIDToArrayInCookie("poems", id)) {
        display_error("You can only save a poem once per device. You might already have this ID saved. Press Control-F and you may be able to search for it through the poems displayed below.", 350)
      } else {
        document.querySelector(".id_code").value = "";
        refreshPoems();
      }
    } else {
      display_error("Unknown Error");
    }
  });
};
var display_error = async(msg, width=500, animation=true) => {
  const warning_dom = document.querySelector(".warning");
  document.querySelector(".warning_msg").innerHTML = msg;
  show(document.getElementById("warning_logo"));
  hide(document.getElementById("checkmark_logo"));
  warning_dom.style.maxWidth = width + "px";
  show(warning_dom);
  dead_drop_background.style.display = "flex";
  
  queue_new_animation(warning_dom, "animate__bounceIn", 800);

  return new Promise((resolve) => {
    const close = warning_dom.querySelector('.close');

    function handleCloseClick() {
      cleanUp();
      resolve(false);
    }
    function cleanUp() {
      close.removeEventListener('click', handleCloseClick);
    }
    close.addEventListener('click', handleCloseClick);
   });
}
var display_success = (msg, width=500) => {
  const warning_dom = document.querySelector(".warning");
  document.querySelector(".warning_msg").innerHTML = msg;
  show(document.getElementById("checkmark_logo"));
  hide(document.getElementById("warning_logo"));
  warning_dom.style.maxWidth = width + "px";
  show(warning_dom);
  dead_drop_background.style.display = "flex";

  queue_new_animation(warning_dom, "animate__bounceIn", 800);
};
var confirmation = async(msg, width=500) => {
  const confirmation_dom = document.querySelector(".confirmation");
  document.querySelector(".confirmation_msg").innerHTML = msg;
  confirmation_dom.style.maxWidth = width + "px";
  show(confirmation_dom);
  dead_drop_background.style.display = "flex";

  queue_new_animation(confirmation_dom, "animate__bounceIn", 800);

  return new Promise((resolve) => {
    const close = document.getElementById('no_close');
    const no = document.getElementById('no');
    const yes = document.getElementById('yes');

    function handleYesClick() {
      cleanUp();
      resolve(true);
      close.click();
    }
    function handleNoClick() {
      cleanUp();
      resolve(false);
      close.click();
    }
    function handleCloseClick() {
      cleanUp();
      resolve(false);
    }
    function cleanUp() {
      yes.removeEventListener('click', handleYesClick);
      no.removeEventListener('click', handleNoClick);
      close.removeEventListener('click', handleCloseClick);
    }
    yes.addEventListener('click', handleYesClick);
    no.addEventListener('click', handleNoClick);
    close.addEventListener('click', handleCloseClick);
   });
}
var input = async(msg, width=500, type="input") => {
  const input_dom = document.querySelector(".input_box");
  document.querySelector(".input_msg").innerHTML = msg;
  input_dom.style.maxWidth = width + "px";
  show(input_dom);
  dead_drop_background.style.display = "flex";

  queue_new_animation(input_dom, "animate__bounceIn", 800);

  const input_box = input_dom.querySelector('input');

  input_box.type = type;
  input_box.focus();

  return new Promise((resolve) => {
    const close = document.getElementById("no_input_close");
    const submit_input_box = document.querySelector(".submit_input_box");

    function handleCloseClick() {
      cleanUp();
      resolve(false);
    }
    function handleSubmitClick() {
      const value = input_box.value;
      cleanUp();
      resolve(value);
      close.click();
    }
    function handleEnter(event) {
      const value = input_box.value;
      if(event.which == 13) {
        cleanUp();
        resolve(value);
        close.click();
      }
    }
    function cleanUp() {
      input_box.removeEventListener('keydown', handleEnter);
      submit_input_box.removeEventListener('click', handleSubmitClick);
      close.removeEventListener('click', handleCloseClick);
      input_box.value = "";
    }
    input_box.addEventListener('keydown', handleEnter);
    submit_input_box.addEventListener('click', handleSubmitClick);
    close.addEventListener('click', handleCloseClick);
   });
}

var buttons = document.getElementsByTagName('button');
Array.prototype.forEach.call(buttons, function(b){
  b.addEventListener('click', createRipple);
});
function createRipple(e)
{
  if(this.getElementsByClassName('ripple').length > 0)
    {
      this.removeChild(this.childNodes[1]);
    }
  
  var circle = document.createElement('div');
  this.appendChild(circle);
  var rect = this.getBoundingClientRect();

  var d = Math.max(this.clientWidth, this.clientHeight);
  circle.style.width = circle.style.height = d + 'px';
  circle.style.left = e.clientX - rect.left - d / 2 + 'px';
  circle.style.top = e.clientY - rect.top - d / 2 + 'px';
  circle.classList.add('ripple');

  setTimeout(function() {
    circle.remove();
  }, 1000);
}
const toggleButton = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sidebar');
let isOpen = false;
let startX = null;
let currentX = null;

toggleButton.addEventListener('click', toggleSidebar);

// Close sidebar if clicked outside
document.addEventListener('click', (event) => {
  if (!sidebar.contains(event.target) && isOpen) {
    closeSidebar();
  }
});
// Swipe to open and close sidebar
document.addEventListener('touchstart', (e) => {
  startX = e.touches[0].clientX;
});
document.addEventListener('touchmove', (e) => {
  if (startX !== null) {
    currentX = e.touches[0].clientX;
    const deltaX = currentX - startX;
    // Check if sidebar is closed and swipe to open
    if (!isOpen && deltaX > 50) { // Swipe right threshold
      openSidebar();
      startX = null;
    }
    // Check if sidebar is open and swipe to close
    if (isOpen && deltaX < -50) { // Swipe left threshold
      closeSidebar();
      startX = null;
    }
  }
});
document.addEventListener('touchend', () => {
  startX = null;
});
function toggleSidebar() {
  if (isOpen) {
    closeSidebar();
  } else {
    openSidebar();
  }
}
async function openSidebar(animation=true) {
  if(!animation) {
    sidebar.classList.add('no_animate');
  }
  setCookie("sideBar", "open");
  sidebar.classList.remove('closed');
  toggleButton.textContent = '🢔';
  await timeout(300);
  sidebar.classList.remove('no_animate');
  isOpen = true;
}
async function closeSidebar(animation=true) {
  if(!animation) {
    sidebar.classList.add('no_animate');
  }
  setCookie("sideBar", "closed");
  sidebar.classList.add('closed');
  toggleButton.textContent = '🢖';
  await timeout(300);
  sidebar.classList.remove('no_animate');
  isOpen = false;
}
if(getCookie("sideBar") == "open") {
  openSidebar(false);
} else if(getCookie("sideBar") == "closed") {
  closeSidebar(false);
} else {
  if(window.innerWidth >= 573) {
    openSidebar(false);
  }
}
var side_buttons = document.querySelectorAll("#tab");
var current_window_id = 0;
var inAnimation = false;

var change_page = (tag, animation=true) => {
  var new_window_id = tag;
  if(new_window_id == current_window_id) return;

  if(inAnimation) {
    setTimeout(function() {
      change_page(tag);
    }, 100);
    return;
  }
  if(animation) {
    if(new_window_id > current_window_id) {
      var first_animation = "animate__fadeOutUp";
      var second_animation = "animate__fadeInUp";
    } else {
      var first_animation = "animate__fadeOutDown";
      var second_animation = "animate__fadeInDown";
    }
    inAnimation = true;
  
    var page_old = document.querySelector(".page_" + current_window_id);
    queue_new_animation(page_old, first_animation, 1100).then( () => {
      hide(page_old);
    });
    var page_new = document.querySelector(".page_" + new_window_id);
    page_new.style.display = "flex";
    queue_new_animation(page_new, second_animation, 1100).then( () => {
      inAnimation = false;
    });
  } else {
    var page_old = document.querySelector(".page_" + current_window_id);
    hide(page_old);
    var page_new = document.querySelector(".page_" + new_window_id);
    page_new.style.display = "flex";
  }
  current_window_id = new_window_id;
  if(on_page_function_array[tag] != undefined) { 
    on_page_function_array[tag]();
  }
};
for(var i = 0; i < side_buttons.length; i++) {
  side_buttons[i].onclick = function() { 
    if(window.innerWidth < 573) {
      closeSidebar();
    }
  }
}
var change_page_hash = (hash, animation=true) => {
  const hash_dictionary = {"#home": 0, "#my-poems": 1, "#about": 2, "#faq": 3, "#contact": 4, "#login": 5, "#poem-swipper": 6, "#database": 8, "#issues": 9, "#users": 10, "#print-queue": 11};
  var tag = hash_dictionary[hash];
  if(typeof tag !== "undefined") {
    change_page(tag, animation);
  }
}
/* was part of approve.js */
const approve_container = document.querySelector(".approve_container");
var load_new_poem_data = async () => {
  try {
    const response = await make_request({}, "./commands/approve.php");

    if (response.status == 400) {
      display_error(response.text, 400);
      return null;
    }
    return JSON.parse(response.text);
  } catch (error) {
    console.error('There was a problem with the request:', error);
    display_error('Error: Unable to load data', 500);
    return null;
  }
};
var approve_poem = (id) => {
  make_request({approve: id}, "./commands/approve.php");
  page_6.overflow = "hidden";
  queue_new_animation(approve_container, "animate__fadeOutRightBig").then(() => {
    approve_container.querySelector(".write_container").remove();
    page_6.overflow = "auto";
    load_new_poem_data().then((data) => {
      load_new_poem(data, "animate__fadeInLeft");
    });
  });
}
var reject_poem = (id) => {
  make_request({reject: id}, "./commands/approve.php");
  page_6.overflow = "hidden";
  queue_new_animation(approve_container, "animate__fadeOutLeftBig").then(() => {
    approve_container.querySelector(".write_container").remove();
    page_6.overflow = "auto";
    load_new_poem_data().then((data) => {
      load_new_poem(data, "animate__fadeInRight");
    });
  });
}
const page_5 = document.querySelector(".page_5");
const page_6 = document.querySelector(".page_6");
var load_new_poem = (info_array, animation=true) => {
  if(!info_array) info_array = {id: "n/a", name: "n/a", title: "No More Poems", poem: "Congraulations you've finished reviewing all the poems (for now ofc)! You can sit back and relax now until you want to come back and check again later.", status: "n/a", timestamp: "n/a", comments: [], printHistory: []};

  approve_container.insertAdjacentHTML("beforeend", createHTMLFromArray(info_array));
  approve_container.querySelector('.write_container').insertAdjacentHTML("beforeend", `<div class="flexbox">
                                                                                        <button class="approval_button reject" id="${info_array.id}">🗷</button>
                                                                                        <button class="approval_button approve" id="${info_array.id}">🗹</button>
                                                                                      </div>`);
  var buttons = approve_container.getElementsByTagName('button');
  Array.prototype.forEach.call(buttons, function(b){
    b.addEventListener('click', createRipple);
  });
  check_flex(approve_container, page_6);
  if(animation) {
    queue_new_animation(approve_container, "animate__bounceIn", 1000);
  }
  approve_container.querySelector(".approve").onclick = function() {
    approve_poem(this.getAttribute("id"));
  }
  approve_container.querySelector(".reject").onclick = function() {
    reject_poem(this.getAttribute("id"));
  }
};
const login_container = document.getElementById("login_container");

on_page_function_array[5] = () => {
  if(logged_in) {
    check_flex(post_login, page_5);
  } else {
    check_flex(login_container, page_5);
  }
}
var logged_in = false;
var next_step_login = (response) => {
  hideBasedOffOfPermission(response.status);
  show(document.getElementById("arrow3"));
  const post_login = document.getElementById("post_login");
  logged_in = true;

  queue_new_animation(login_container, 'animate__bounceOut', 700).then(() => {
    hide(login_container);
    show(post_login);
    queue_new_animation(post_login, 'animate__bounceIn', 700);
    post_login.querySelector("h1").innerHTML = `Welcome ${response.username}!`;
    post_login.querySelector("#tier_status").innerHTML = response.status;
    check_flex(post_login, page_5);
  });
}
var moderator_permissions = {"low": [0], "medium": [0, 1, 2, 4], "high": [0, 1, 2, 3, 4]};

var hideBasedOffOfPermission = (status) => {
  const permissions = moderator_permissions[status];
  for(var i = 0; i < permissions.length; i++) {
    var buttons = document.querySelectorAll(".admin_feature_" + permissions[i]);
    for(var j = 0; j < buttons.length; j++) {
      show(buttons[j]);
    }
  } 
}
var hideAllButtons = () => {
  var buttons = document.querySelectorAll(".admin_feature");
  for(var j = 0; j < buttons.length; j++) {
    hide(buttons[j]);
  }
};
function checkLogin() {
  make_request({action: "check_login"}, "./commands/login.php").then((response) => {
    if(response.status != 200) {
      display_error("Error: Server Error. The website may not work entirely.");
      return null;
    }
    var response_object = JSON.parse(response.text);
    if(response_object.logged_in) {
      next_step_login(response_object); 
    }
  });
}
checkLogin();

function isSecurePassword(password) {
  // Enforce password length
  const minLength = 8;

  // Check password length
  if (password.length < minLength) {
      return `Password must be at least ${minLength} characters long.`;
  }
  
  // Check for at least one uppercase letter
  if (!/[A-Z]/.test(password)) {
      return "Password must contain at least one uppercase letter.";
  }
  
  // Check for at least one lowercase letter
  if (!/[a-z]/.test(password)) {
      return "Password must contain at least one lowercase letter.";
  }
  
  // Check for at least one number
  if (!/[0-9]/.test(password)) {
      return "Password must contain at least one number.";
  }
  
  // Check for at least one special character
  if (!/[\W_]/.test(password)) {
      return "Password must contain at least one special character.";
  }

  return true;
}
async function tryAgainPassword(username, password) {
  var password_1 = await input("Type in password", 300, "password", false);
  if(password_1) {
    var securePassword = isSecurePassword(password_1);
    if(securePassword !== true) {
      await display_error(securePassword, 350);
      return tryAgainPassword(username, password);
    }
    var password_2 = await input("Type in retype password", 300, "password", false);
    if(password_1 != password_2) {
      return tryAgainPassword(username, password);
    } else {
      return trySetPassword(username, password_1);
    }
  } else if(password_1 === false) {
    return false;
  } else {
    await display_error("Please enter a password!", 300);
    return tryAgainPassword(username, password);
  }
}
async function trySetPassword(username, password) {
  var response = await make_request({action: "set_password", username: username, password: password}, "./commands/login.php");
  if(response.status != 200) {
    await display_error(response.text, 300);
    return tryAgainPassword(username, password);
  } else {
    checkLogin();
    return true;
  }
}
async function setPassword() {
  const name_box = document.getElementById("login_name");
  const password_box = document.getElementById("password");

  var username = name_box.value;
  var password = password_box.value;

  return trySetPassword(username, password);
}
// Submit an XHTML request to verfiy login information and to obtain database info.
async function login() {
  const name_box = document.getElementById("login_name");
  const password_box = document.getElementById("password");
  const login_btn = document.getElementById("login");

  var username = name_box.value;
  var password = password_box.value;

  if(!username || !password) {
    display_error("Please enter in a username or password!", 300);
    return false;
  }
  login_btn.disabled = true;

  var response = await make_request({ action: "login", username: username, password: password }, "./commands/login.php");
  login_btn.disabled = false;

  var response_object = JSON.parse(response.text);
  if (response.status == 401) {
    display_error('Error: ' + response_object.message, 400);
    return false;
  }
  if(response.status == 403) {
    display_error("Error: " + response_object.message, 400);
    return false;
  }
  if(response_object.status == "Password not set") {
    var securePassword = isSecurePassword(password);
    if(securePassword !== true) {
      display_error(securePassword, 350);
      return false;
    }
    var new_password = await input("Please confirm the password for your new account.", 400, "password");
    if(!new_password) {
      display_error("Please enter a password!", 300);
      return false;
    }
    if(new_password != password) {
      display_error("Error: Your passwords don't match. Please try again.", 300);
      return false;
    } else {
      setPassword();
      return true;
    }
  }
  next_step_login(response_object);
  name_box.value = "";
  password_box.value = "";

  return true;
}
var logout = async() => {
  var response = await make_request({action: "logout"}, "./commands/login.php");
  if(response.status != 200) {
    display_error("Error: Could not sign out");
    return null;
  }
  hide(post_login);
  hide(document.getElementById("arrow3"));
  hide(document.getElementById("sub_buttons3"));

  show(login_container);
  hideAllButtons();
  window.location.hash = "#login";
}
var loadPoemSwipper = (animation=true) => {
  // If there's a write_container, get rid of it.
  if(approve_container.querySelector(".write_container")) {
    approve_container.querySelector(".write_container").remove();
  }
  load_new_poem_data().then((data) => {
    load_new_poem(data, animation);
  });
}
on_page_function_array[6] = () => loadPoemSwipper(false);
// When the login button is clicked, login to the website.
document.getElementById("login").onclick = () => login();

// When enter is tapped on the keyboard, login to the website.
const inputs = document.querySelectorAll("login_input");
for (let i = 0; i < inputs.length; i++) {
  inputs[i].onkeydown = (e) => {
    if(e.which === 13) onSubmit();
  }
}
document.getElementById('arrow3').addEventListener('click', async function() {
  var subButtons = document.getElementById('sub_buttons3');
  var arrow = document.getElementById('arrow3');

  if (subButtons.style.display === 'block') {
    arrow.textContent = '⮟';
    await queue_new_animation(subButtons, 'animate__bounceOut', 900);
    hide(subButtons);
  } else {
    show(subButtons);
    arrow.textContent = '⮝';
    await queue_new_animation(subButtons, 'animate__bounceIn', 900);
  }
});

const page_8 = document.querySelector(".page_8");
const poem_database = document.getElementById("poem_list_container");
var displayPoemDatabase = (array, destroy=false) => {
  if(destroy) {
    poem_database.innerHTML = "";
  }
  if(array.length != 0) {
    var all_html = "";
    for(var i = array.length - 1; i >= 0; i--) {
      if(array[i]) {
        all_html += createHTMLFromArray(array[i], true);
      }
    }
    poem_database.insertAdjacentHTML("beforeend", all_html);

    var buttons = poem_database.getElementsByTagName('button');
    Array.prototype.forEach.call(buttons, function(b){
      b.addEventListener('click', createRipple);
    });
    poem_database.style.display = "flex";
    poem_database.style.textAlign = "left";
  } else if(destroy) {
    poem_database.style.display = "block";
    poem_database.style.textAlign = "center";
    poem_database.innerHTML = "<p>Sorry no poems found.</p>";
  }
  check_flex(page_8.querySelector(".container"), page_8);
};
var global_search = "";
var searchDatabase = async (query = "", execute=true) => {
  if(!query) {
    global_search = await input("Please enter a search query like title, author, or ID and we'll perform a search.", 320);
  } else {
    global_search = query;
  }
  if(global_search) {
    if(execute) {
      load_database_page(1, true);
    }
    document.getElementById(current_database.toLowerCase() + "_btn").classList.remove("selected");
  }
}
const containers = document.querySelectorAll('.section_container');
containers.forEach(container => {
  const buttons = container.querySelectorAll('.option');

  buttons.forEach(button => {
    button.addEventListener('click', () => {
      buttons.forEach(btn => btn.classList.remove('selected'));
      button.classList.add('selected');
    });
  });
});
var load_database_page = async function(page=1, destroy=true) {
  if(destroy) {
    poem_database.style.opacity = 0.4;
  }
  return new Promise((resolve, reject) => {
    make_request({table: current_database, page: page, search: global_search}, "./commands/fetchDatabase.php").then((response) => {
      if(response.status != 200) {
        display_error("Error: Could not load database");
        return;
      }
      var poem_content = JSON.parse(response.text);
      total_scroll_pages = poem_content.totalPages;
      current_scroll_page = poem_content.currentPage;
      if(destroy) {
        poem_database.style.opacity = 1;
      }
      displayPoemDatabase(poem_content.poems, destroy);
      resolve(); // Resolve after loading and displaying content
    }).catch((error) => {
      reject(error); // Reject if there's an error during the request
    });
  });
}
var current_database = getCookie("selected_db") != null ? getCookie("selected_db") : "Pending";
try {
  document.getElementById(current_database.toLowerCase() + "_btn").classList.add("selected");
} catch(e) {
  display_error(e);
  current_database = "Pending";
}

var selectDatabase = function(database, destroy=false) {
  global_search = "";
  current_database = database;
  setCookie("selected_db", current_database);
  load_database_page(1, destroy);
}
on_page_function_array[8] = load_database_page;

var scroll_loading = false;
var total_scroll_pages = 1;
var current_scroll_page = 1;
const loadMoreContent = async () => {
  if(current_scroll_page < total_scroll_pages) {
    await load_database_page(current_scroll_page + 1, false);
  } else if(total_scroll_pages < current_scroll_page) {
    var conf = await confirmation("The automatic poem loading system has lost track of its place. Would you like to reload?", 300);
    if(conf) {
      load_database_page(1, true);
    }
  }
};
const handleScroll = async () => {
  if (scroll_loading) return;

  const scrollTop = page_8.scrollTop;
  const scrollHeight = page_8.scrollHeight;
  const clientHeight = page_8.clientHeight;
  const threshold = 100; // Trigger 50px before the bottom of #page_8

  if (scrollTop + clientHeight >= scrollHeight - threshold) {
    scroll_loading = true;
      await loadMoreContent();
      scroll_loading = false;
  }
};
var disableAllMoveButtons = (e) => {
  const container = e.parentElement;
  const buttons = container.querySelectorAll("button");
  for(var i = 0; i < buttons.length; i++) {
    buttons[i].disabled = true;
  }
}
var delete_comment = async (id, index, obj) => {
  var conf = await confirmation("Are you sure you wish to delete this comment? This operation cannot be undone.", 300);
  if(!conf) return;
  obj.disabled = true;
  var req = await make_request({action: "deleteComment", poemId: id, commentIndex: index}, "./commands/fetchDatabase.php");
  if(req.status != 200) {
    display_error("Error: Could not delete comment", 300);
    obj.disabled = false;
    return;
  }
  obj.parentElement.parentElement.remove();
}
var deletePoemDOM = (e) => {
  e.parentElement.parentElement.parentElement.remove();
  var flex_status = check_flex(page_8.querySelector(".container"), page_8);
  // Sometimes deleting poems rapidly can lead to a situations where poems from other pages won't be loaded.
  // This fixes that issue.
  if(poem_database.innerHTML == "") {
    load_database_page(1, true);
  } else if(!flex_status) {
    loadMoreContent();
  }
}
var movePoem = async (id, move_to, e) => {
  if(move_to == "Deleted") {
    var conf = await confirmation("Are you sure you want to delete this poem? This action will be logged and the poem will be marked as trash and automatically deleted in 30 days.", 350);
    if(!conf) return;
  }
  disableAllMoveButtons(e);
  make_request({action: "move", poemId: id, target_table: move_to}, "./commands/fetchDatabase.php").then((response) => {
    if(response.status == 200) {
      deletePoemDOM(e);
    } else {
      display_error('Error: Could not move poem.', 400);
    }
  });
}
var deletePoem = async (id, e) => {
  var conf = await confirmation("Are you sure you want to permanently delete this poem? This change cannot be undone.", 300);
  if(conf) {
    disableAllMoveButtons(e);
    make_request({action: "delete_poem", poemId: id}, "./commands/fetchDatabase.php").then((response) => {
      if(response.status == 200) {
        deletePoemDOM(e);
      } else if(response.status == 400) {
        display_error(response.text, 400);
      } else {
        display_error('Error: Could not delete poem.', 400);
      }
    });
  }
}
page_8.addEventListener('scroll', handleScroll);

var buildUserHierarchy = (data) => {
  // Create a map to store nodes by their id
  const nodes = new Map();
  data.forEach(item => {
      nodes.set(item.id, { ...item, children: [] });
  });
  let hierarchy = [];
  data.forEach(item => {
      if (item.parent_id === null) {
          hierarchy.push(nodes.get(item.id));
      } else {
          const parent = nodes.get(item.parent_id);
          if (parent) {
              parent.children.push(nodes.get(item.id));
          }
      }
  });
  return hierarchy;
}
var yourId = "1"; // Change this to the ID of the user to highlight

var buildHierarchy = (data) => {
    const nodes = new Map();
    data.forEach(item => {
        nodes.set(item.id, { ...item, children: [] });
    });

    let hierarchy = [];

    data.forEach(item => {
        if (item.parent_id === null) {
            hierarchy.push(nodes.get(item.id));
        } else {
            const parent = nodes.get(item.parent_id);
            if (parent) {
                parent.children.push(nodes.get(item.id));
            }
        }
    });

    return hierarchy;
}

var createHtmlList = (items, isDescendantOfYou = false) => {
  if (items.length === 0) {
      return '';
  }

  const ul = document.createElement('ul');
  items.forEach(item => {
      const li = document.createElement('li');
      li.innerHTML = `${item.username}`;

      if (item.banned === "1") {
          li.classList.add('banned');
      }
      var isDescendant = false;
      if (item.id === yourId) {
          li.innerHTML = `<b style='color: orange'>${li.textContent}</b>`;
          isDescendant = true; // All children of this node will also be descendants of "you"
      }
      li.innerHTML += ` — <i>${item.tier} tier</i>`;
      if (isDescendantOfYou) {
          const banButton = document.createElement('button');
          banButton.textContent = item.banned === "1" ? 'Unban' : 'Ban';
          banButton.onclick = async () => {
              var operation = item.banned === "1" ? 'unban' : 'ban';
              var conf = await confirmation(`Are you sure you wish to ${operation} ${item.username}? This change is not permanent and can be reversed at anytime.`, 300);
              if(!conf) return;

              var req = await make_request({action: operation, user_id: item.id}, "./commands/fetchUsers.php");
              if(req.status != 200) {
                display_error("Error: Could not ban user");
                return;
              }

              item.banned = item.banned === "1" ? "0" : "1"; // Toggle banned state
              li.classList.toggle('banned', item.banned === "1"); // Toggle class based on banned state
              banButton.textContent = item.banned === "1" ? 'Unban' : 'Ban';
          };
          li.appendChild(banButton);
      }
      const childrenUl = createHtmlList(item.children, isDescendantOfYou || isDescendant);
      if (childrenUl) {
          li.appendChild(childrenUl);
      }
      ul.appendChild(li);
  });
  return ul;
}
const page_10 = document.querySelector(".page_10");
var loadUsers = async () => {
  var response = await make_request({action: "get_all_users"}, "./commands/fetchUsers.php");
  const user_info_container = document.querySelector(".user_info_container");
  var array = JSON.parse(response.text);
  yourId = array.user_id.toString();
  var hierarchy = buildUserHierarchy(array.users);
  var htmlHierarchy = createHtmlList(hierarchy);
  user_info_container.innerHTML = "";
  user_info_container.appendChild(htmlHierarchy);
  check_flex(page_10.querySelector(".container"), page_10);
  return true;
}
on_page_function_array[10] = loadUsers;

document.getElementById("create_account").onclick = async () => {
  const account_create_username = document.getElementById("account_create_username");
  const tierDropdown = document.getElementById("tierDropdown");

  if(account_create_username.value == "") {
    display_error("Please enter in a username");
    return;
  }
  var conf = await confirmation("Are you sure you want to create an account? As a security precaution it will be deleted in 15 minutes if a password is not set.", 400);
  if(!conf) return;

  const account_name = account_create_username.value;
  account_create_username.value = "";

  var request = await make_request({action: "create_account", username: account_name, tier: tierDropdown.value}, "./commands/fetchUsers.php");
  if(request.status != 200) {
    display_error(request.text);
  }
  loadUsers();
}
var createIssuesTableHTML = (issues) => {
    // Create table and table header
    let table = '<table border="1" style="border-collapse: collapse; width: 100%;">';
    table += `<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Issue</th>${(issue_status == "open") ? '<th>Action</th>' : ''}</tr></thead>`;
    table += '<tbody>';

    // Add rows to table
    issues.forEach(issue => {
        table += `<tr id="issue-${issue.id}">
            <td>${issue.id}</td>
            <td>${convertToSpecialChars(issue.name)}</td>
            <td>${convertToSpecialChars(issue.email)}</td>
            <td>${convertToSpecialChars(issue.subject)}</td>
            <td class='issue'>${convertToSpecialChars(issue.issue).replace(/\n/g, '<br>')}</td>
            ${(issue_status == "open") ? `<td><button class="generic_button smaller_button" onclick="closeIssue(${issue.id}, this)">Close</button></td>` : '' }
        </tr>`;
    });

    table += '</tbody></table>';

  return table;
}
// Function to close an issue
var closeIssue = async (id, object) => {
  var conf = await confirmation("Are you sure you want to close this ticket? You won't be able to undo this operation. You'll still be able to view this ticket for 30 days before it's automatically deleted from the database.", 400);
  if(!conf) return;

  const parameters = {
      'id': id,
      'action': 'close'
  };
  object.disabled = true;
  
  const response = await make_request(parameters, './commands/fetchIssues.php');

  if (response) {
      if (response.status === 200) {
          const row = document.getElementById(`issue-${id}`);
          if (row) {
              row.parentNode.removeChild(row);
          }
      } else {
          display_error('Error closing issue: ' + response.text);
      }
  }
}
const page_9 = document.querySelector(".page_9");
var issue_status = getCookie("issue_status") ? getCookie("issue_status") : "open";
document.getElementById(issue_status.toLowerCase() + "_btn").classList.add("selected");
var loadIssues = async (status) => {
  if(typeof status != 'undefined') {
    issue_status = status;
    setCookie("issue_status", issue_status);
  }
  const issues_box = document.getElementById("issues");
  issues_box.style.opacity = 0.4;
  var response = await make_request({status: issue_status}, "./commands/fetchIssues.php");
  if(response.status != 200) {
    display_error("Error: Cannot load issue box");
    return;
  }
  issues_box.style.opacity = 0.8;
  var response_array = JSON.parse(response.text);
  var tableHTML = createIssuesTableHTML(response_array);
  issues_box.innerHTML = tableHTML;
  check_flex(page_9.querySelector(".container"), page_9);
}
on_page_function_array[9] = loadIssues;

const page_11 = document.querySelector(".page_11");

var deleteQueue = async (id, obj) => {
  var conf = await confirmation("Are you sure you wish to delete this item from the queue?", 300);
  if(!conf) return;
  obj.disabled = true;
  make_request({action: "remove", queue_id: id}, "./commands/queueManager.php").then(() => {
    obj.disabled = false;
    loadQueue();
  });
}
var moveUpQueue = async (id, obj) => {
  obj.disabled = true;
  make_request({action: "move_up", queue_id: id}, "./commands/queueManager.php").then(() => {
    obj.disabled = false;
    loadQueue();
  });
}
var moveDownQueue = async (id, obj) => {
  obj.disabled = true;
  make_request({action: "move_down", queue_id: id}, "./commands/queueManager.php").then(() => {
    obj.disabled = false;
    loadQueue();
  });
}
var moveTopQueue = async (id, obj) => {
  obj.disabled = true;
  make_request({action: "move_top", queue_id: id}, "./commands/queueManager.php").then(() => {
    obj.disabled = false;
    loadQueue();
  });
}

var displayQueue = (queue) => {
  // Get the list container element
  const listContainer = document.getElementById('queueList');

  // Clear the list container
  listContainer.innerHTML = '';

  if(!queue.length) {
    listContainer.innerHTML = 'No items in queue';
  }

  // Iterate through the queue array and create list items
  queue.forEach(item => {
      // Create a list item element
      const listItem = document.createElement('div');
      listItem.classList.add("action-buttons");

      // Set the content of the list item
      // Items are repeated to keep the width of the container constant
      listItem.innerHTML = `<b>#${item.queue_number})</b> <a href="#database" style="color: orange" onclick="searchDatabase('${item.poem_id}', false)"><b>${item.title}</b></a> written by <a href="#database" style="color: lightblue" onclick="searchDatabase('${item.author}', false)"><b>${item.author}</b></a> — added by ${item.added_by}
                            ${(Number(item.queue_number) == 1) ? '' : `<button style="padding: 0 5px;" class="symbols_font" onclick="moveUpQueue(${item.id}, this)">⮝</button>`}
                            ${(Number(item.queue_number) == queue.length) ? '' : `<button style="padding: 0 5px;" class="symbols_font" onclick="moveDownQueue(${item.id}, this)">⮟</button>`}
                            ${(Number(item.queue_number) == 1) ? '' : `<button style="padding: 0 5px;" class="symbols_font" onclick="moveTopQueue(${item.id}, this)">⬤</button>`}
                            <button class="delete" style="padding: 0 5px;" onclick="deleteQueue(${item.id}, this)">x</button>
                            ${(Number(item.queue_number) == 1) ? '<button style="padding: 0 5px; pointer-events: none; visibility: hidden;" class="symbols_font">⮝</button>' : ''}
                            ${(Number(item.queue_number) == queue.length) ? '<button style="padding: 0 5px; pointer-events: none; visibility: hidden;" class="symbols_font">⮟</button>' : ''}
                            ${(Number(item.queue_number) == 1) ? `<button style="padding: 0 5px; pointer-events: none; visibility: hidden;" class="symbols_font">⬤</button>` : ''}
                            `;

      // Append the list item to the list container
      listContainer.appendChild(listItem);
  });
  check_flex(page_11.querySelector(".container"), page_11);
}
var loadQueue = () => {
  make_request({action: "view"}, "./commands/queueManager.php").then((response) => {
    var response_object = JSON.parse(response.text); 
    displayQueue(response_object);
  });
}
on_page_function_array[11] = loadQueue;

window.addEventListener('hashchange', function() {
  change_page_hash(window.location.hash);
});
    // Load the initial content based on the hash
if(window.location.hash) {
    change_page_hash(window.location.hash, false);
} else {
    window.location.hash = '#home';
}
