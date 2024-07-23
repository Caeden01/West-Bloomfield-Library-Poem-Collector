<?php 
	require_once("./include.php");
	?>
<!DOCTYPE html>
<html lang="en">
    <head>
		<meta charset="UTF-8">
		<meta name="description" content="Explore and contribute to the <?php echo library_name; ?> Poem Collector, a community-driven platform for sharing and collecting poems. Discover new poems and share your own creativity.">
		<meta name="keywords" content="poetry, poems, poem collection, community poems, share poems, creative writing, literature, poem collector">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Poem Submission</title>
        <link rel="stylesheet" href="./styling/animate.min.css" />
        <script src="./scripts/qrious.js"></script>
        <link rel="stylesheet" href="./styling/submission_page.css" />
        <script>
            const library_name = "<?php echo library_name; ?>";
            const library_logo_bw = "<?php echo library_logo_bw; ?>";
            const url_comment_path = "<?php echo url_comment_path; ?>";
            const qr_code_quality = "<?php echo qr_code_quality; ?>";
			const qr_code_path = "<?php echo website_qr_code; ?>";
        </script>
        <?php if(use_captcha) { ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php } ?>
        <?php 
			if(!use_captcha) {?>
        <script>
            var fakeCaptcha = function () {
                this.reset = () => {
                    // do nothing
                };
                this.execute = () => {
                    onSubmit();
                };
            };
            var grecaptcha = new fakeCaptcha();
        </script>

        <?php }	?>
    </head>
    <body>
        <?php if(use_captcha) { ?>
        <div class="g-recaptcha" style="display: none;" data-sitekey="<?php echo captcha_public_key; ?>" data-callback="onSubmit" data-size="invisible"></div>
        <?php } ?>
        <div class="dead_drop_background animate__animated">
            <div class="container warning animate__animated">
                <div class="toolbar" style="text-align: right; font-size: 30px;">
                    <span class="close"><b>🞫</b></span>
                </div>
                <div class="flexbox" style="padding: 0px 20px 40px 20px;">
                    <img src="./images/warning.png" style="width: 80px; margin-right: 15px;" id="warning_logo" />
                    <img src="./images/checkmark.png" style="width: 80px; margin-right: 15px; display: none;" id="checkmark_logo" />
                    <div class="warning_msg">*Error*</div>
                </div>
            </div>
            <div class="container confirmation animate__animated">
                <div class="toolbar" style="text-align: right; font-size: 30px;">
                    <span class="close" id="no_close"><b>🞫</b></span>
                </div>
                <div style="padding: 0px 20px 30px 20px;">
                    <div class="confirmation_msg">*Msg here*</div>
                    <div class="action-buttons flexbox">
                        <button class="delete padding" id="no">No</button>
                        <button class="padding" id="yes">Yes</button>
                    </div>
                </div>
            </div>
            <div class="container input_box animate__animated">
                <div class="toolbar" style="text-align: right; font-size: 30px;">
                    <span class="close" id="no_input_close"><b>🞫</b></span>
                </div>
                <div style="padding: 0px 20px 30px 20px;">
                    <div class="input_msg">*Msg here*</div>
                    <div class="action-buttons flexbox" style="margin-top: 20px;">
                        <input type="text" class="generic_input wide" style="margin: 0px; margin-right: 10px;" placeholder="Enter Here..." />
                        <button style="width: 100px; padding: 10px 0;" class="submit_input_box">Submit</button>
                    </div>
                </div>
            </div>
            <div class="container mid_wide_mid_tall animate__animated" id="terms_and_conditions" style="display: none;">
                <div class="toolbar" style="text-align: right; font-size: 30px;">
                    <span class="close"><b>🞫</b></span>
                </div>
                <h1>Terms and Conditions</h1>
                <h2>These terms and conditions outline the rules and regulations for the use of our website.</h2>
                <p>By accessing this website we assume you accept these terms and conditions in full. Do not continue to use our website if you do not accept all of the terms and conditions stated on this page.</p>
                <h2>1. Submission of Poems</h2>
                <ul>
                    <li>By submitting a poem to our website, you affirm that the content is your own original work.</li>
                    <li>By submitting a poem, you grant us the right to display and print the poem on our thermal printer.</li>
                    <li>Some poems may be visible to and shared with other users on the website.</li>
                </ul>
                <h2>2. Rights and Ownership</h2>
                <ul>
                    <li>
                        We do not claim ownership of the poems submitted to our website. However, by submitting a poem, you grant us a non-exclusive, royalty-free, perpetual, and worldwide license to use, reproduce, distribute for the
                        purposes of operating our website and service.
                    </li>
                </ul>
                <h2>3. User Conduct</h2>
                <ul>
                    <li>Users are prohibited from submitting poems that violate any laws, infringe upon the rights of others, or are otherwise inappropriate as determined by us.</li>
                    <li>We reserve the right to reject any poem submissions that we deem inappropriate or unsuitable for printing.</li>
                    <li>Inappropriate poems include, but are not limited to, those containing offensive language, hate speech, or explicit content.</li>
                    <li>We discourage the submission of AI-generated content but do not explicitly ban it.</li>
                </ul>
                <h2>4. Privacy</h2>
                <ul>
                    <li>We collect certain personal information for the purposes of operating our website and providing our services.</li>
                    <li>
                        The data collected may include:
                        <ul>
                            <li>Name (required for submission)</li>
                            <li>Email address (optional, if submitted)</li>
                            <li>Device Information</li>
                        </ul>
                    </li>
                    <li>
                        We use this information to:
                        <ul>
                            <li>Operate and maintain the website</li>
                            <li>Communicate with users, if necessary</li>
                            <li>Analyze and improve our services</li>
                            <li>Ensure security and prevent abuse</li>
                        </ul>
                    </li>
                    <li>We do not sell or rent your personal information to third parties. However, we may disclose your information if required by law or to protect our rights.</li>
                </ul>
                <h2>5. Cookies</h2>
                <p>
                    We use cookies on our website to enhance user experience. Cookies are small files stored on your computer or mobile device which collect and store information about your browsing activities. By using our website, you
                    consent to the use of cookies in accordance with our Cookie Policy.
                </p>
                <h2>6. Disclaimer</h2>
                <p>
                    We make every effort to ensure the accuracy and reliability of the information provided on our website. However, we make no representations or warranties of any kind, express or implied, regarding the completeness,
                    accuracy, reliability, suitability, or availability of the website or the information, products, services, or related graphics contained on the website for any purpose.
                </p>
                <h2>7. Limitation of Liability</h2>
                <p>
                    In no event shall we be liable for any direct, indirect, punitive, incidental, special, or consequential damages arising out of or in any way connected with the use of our website or with the delay or inability to use
                    the website, or for any information, products, and services obtained through the website, whether based on contract, tort, strict liability, or otherwise.
                </p>
                <h2>8. Changes to Terms and Conditions</h2>
                <p>We reserve the right to revise these terms and conditions at any time without notice. By using this website, you are agreeing to be bound by the current version of these terms and conditions.</p>
                <h2>9. Governing Law</h2>
                <p>
                    These terms and conditions are governed by and construed in accordance with the laws of the United States, and any disputes relating to these terms and conditions will be subject to the exclusive jurisdiction of the
                    courts of the United States.
                </p>
            </div>
        </div>
        <div class="global_container">
            <div class="side_bar closed" id="sidebar">
                <button class="toggle_button" id="toggleSidebar">🢖</button>
                <div class="side_bar_content">
                    <a href="<?php echo library_website; ?>"><img src="<?php echo library_logo; ?>" alt="Library Logo" style="width: 300px;" /></a>
                    <div class="main_button_container">
                        <a href="#home"><button class="side_button" id="tab">Home 🏠</button></a>
                    </div>
                    <div class="main_button_container">
                        <a href="#my-poems"><button class="side_button" id="tab">My Poems 🗐</button></a>
                    </div>
                    <div class="main_button_container">
                        <a href="#about"><button class="side_button" id="tab">About the Project</button></a>
                    </div>
                    <div class="main_button_container">
                        <a href="#faq"><button class="side_button" id="tab">Frequently Asked Questions</button></a>
                    </div>
                    <div class="main_button_container">
                        <a href="#contact"><button class="side_button" id="tab">Contact Moderators</button></a>
                    </div>
                    <div class="main_button_container">
                        <a href="#login"><button class="side_button" id="tab">Approve Poems 🗹</button></a>
                        <button class="arrow_button" id="arrow3" style="display: none;">⮟</button>
                    </div>
                    <div class="sub_buttons animate__animated" id="sub_buttons3" style="padding-bottom: 10px; display: none;">
                        <a href="#poem-swipper"><button class="sub_button admin_feature admin_feature_0" id="tab">Poem Swiper</button></a>
                        <a href="#database"><button class="sub_button admin_feature admin_feature_1" id="tab">Poem Database</button></a>
                        <a href="#issues"><button class="sub_button admin_feature admin_feature_2" id="tab">View Reported Issues</button></a>
                        <a href="#users"><button class="sub_button admin_feature admin_feature_3" id="tab">Manage Moderators</button></a>
                        <a href="#print-queue"><button class="sub_button admin_feature admin_feature_4" id="tab">Printer Queue</button></a>
                        <button class="sub_button" onclick="logout()">Log out</button>
                    </div>
                </div>
            </div>
            <div class="window_container page_0 animate__animated" style="display: flex;">
                <div class="container expandable fixed_width_padding animate__animated" id="get_started_container">
                    <form action="" id="get_started_form">
                        <div class="context_info">
                            Welcome to the <?php echo library_name; ?> poem submission page! Please fill out the boxes below to send in your own personal poem!
                        </div>
                        <div class="label">Name <span class="required">*</span></div>
                        <input type="text" class="generic_input" spellcheck="false" required id="name" maxlength="<?php echo $MAX_NAME_LENGTH; ?>" />
                        <div class="label">
                            Email (optional)
                        </div>
                        <input type="email" id="email" class="generic_input" maxlength="<?php echo $MAX_EMAIL_LENGTH; ?>" />
                        <label class="checkbox bounce">
                            <input type="checkbox" required />
                            <svg viewBox="0 0 21 21">
                                <polyline points="5 10.75 8.5 14.25 16 6"></polyline>
                            </svg>
                        </label>
                        <div class="agree_info">
                            I agree to the <span class="terms_and_conditions_button"><b>terms and conditions</b></span>.
                        </div>
                        <button type="submit" class="large_orange_button generic_input">Get Started</button>
                    </form>
                </div>
                <textarea class="textarea_poem_clone"></textarea>
                <!-- Line counter -->
                <div class="container expandable write_container animate__animated" style="display: none;">
                    <div class="flexbox">
                        <img src="<?php echo library_logo_bw; ?>" style="width: 150px; position: relative; margin-right: 10px;" />
                        <p>
                            <?php echo library_name; ?>
                            Poem Printer Receipt!
                        </p>
                    </div>
                    <div class="flexbox">
                        <img src="<?php echo website_qr_code; ?>" style="width: 125px; position: relative; left: 5px; margin-right: 35px;" />
                        <p>Send in your own poem! Scan the QR code to the left!</p>
                    </div>
                    <input type="text" class="title_input_box" placeholder="Write title here..." maxlength="19" />
                    <textarea class="textarea_poem" placeholder="Type Poem Here..."></textarea>
                    <div style="word-wrap: break-word;">
                        <p>Sent in by <span class="submit_name">*Insert Name*</span> on <span class="submit_date">*Insert Date*</span></p>
                    </div>
                    <div class="flexbox">
                        <img src="<?php echo website_qr_code; ?>" style="width: 125px; margin-right: 10px;" />
                        Do you like the poem you read? Why not leave a comment for the writer? Scan the QR code to the left.
                    </div>
                    <div class="flexbox">
                        <div class="text_limit">0 out of ** lines remaining</div>
                        <button class="submit_poem">&#10148;</button>
                    </div>
                </div>
                <div class="container success_container animate__animated" id="success_container" style="display: none;">
                    <div style="overflow: hidden;">
                        <img src="./images/checkmark.png" style="width: 125px; float: left; margin-right: 10px;" />
                        <div>
                            Congratulations, <span class="submit_name">*NAME*</span>! Your poem has been successfully submitted to our servers. Your poem is referenced with the id:
                            <b><span id="poem_id" style="color: orange;">*ID*</span></b>. I recommend writing this down somewhere so you can check up on information regarding your poem — like its approval status and if its been printed yet
                            — or alternatively you can save it to the browser by clicking save.
                        </div>
                    </div>
                    <div class="flexbox" style="justify-content: center;">
                        <button class="generic_button" onclick="fake_save_poem(this)" id="save_poem_button">Save 🖫</button>
                        <button class="generic_button" onclick="submitNewPoem()">Submit Another ⭯</button>
                    </div>
                </div>
            </div>
            <div class="window_container page_1 animate__animated">
                <div class="container doesnt_get_small_but_not_too_big" id="my_poem_container" style="display: block;">
                    <div class="center_grid_container">
                        <h1>My Poems</h1>
                        <div class="medium_max_width_container">
                            <div class="poem_display_msg">
                                <p>
                                    You currently have no poems saved. You can either
                                    <a href="#home">
                                        <span style="color: orange; cursor: pointer;"><b>send in</b></span>
                                    </a>
                                    and save a poem or you can add one below.
                                </p>
                                <p>Add one here:</p>
                            </div>
                            <div class="label">
                                Add Poem
                            </div>
                            <input type="text" class="generic_input id_code" style="display: inline;" placeholder="ID Code" spellcheck="false" />
                            <button class="generic_button smaller_button" id="add_button" style="margin-top: 0;">Add 🞦</button>
                            <div class="label">
                                Search Through My Poems
                            </div>
                            <input type="text" class="generic_input" style="display: inline;" id="searchMyPoems" placeholder="Title or ID" spellcheck="false" />
                            <button class="generic_button smaller_button" style="margin-top: 0;">Search 🔍</button>
                            <h3>Your poems:</h3>
                        </div>
                    </div>
                    <div class="flexbox poem_box" style="flex-wrap: wrap; display: block; text-align: center;">
                        <p>Sorry no poems found.</p>
                    </div>
                </div>
            </div>
            <div class="window_container page_2 animate__animated">
                <div class="container slightly_small_wide" id="about_container" style="display: block;">
                    <div class="section">
                        <h2 class="text_center">Motivation</h2>
                        <hr />
                        <p>
                            We weren’t the ones to come up with the idea for this project, all that credit goes out to Mr. Ketcham, the Young Adult Services Coordinator at the West Bloomfield Library. He wanted to create an interface that
                            allowed people from the community to write a poem and share it directly with the library, where anyone could receive a copy of it. With a Raspberry Pi and thermal printer in hand, he walked down to Coding Club,
                            where we were excited to hear and start building his wonderful idea.
                        </p>
                    </div>
                    <div class="section">
                        <h2 class="text_center">Brainstorming Process</h2>
                        <hr />
                        <p>
                            Our initial thought process was to create an API where someone could send in their poem, have it received by a Raspberry Pi (a mini computer), and then have it printed on a thermal printer. Although this sounded
                            simple at first, building this system proved to be quite challenging. The first issue that came to mind was: how could we put poems onto the Raspberry Pi? Getting poems was simple — we could use Google Forms. But
                            the challenge was how could we transfer the poems from Google Forms to the Raspberry Pi? Initially, we thought about manually inserting the poems, but we wanted to find a more efficient solution. So we
                            brainstormed another idea: what if instead of using Google Forms, we built our own collection interface through a website? This way we wouldn't need to worry about manually inserting the poems onto the Pi and
                            could automate the process. And that's exactly what we did.
                        </p>
                        <div class="image-container center_grid_container">
                            <img src="./images/plan_layout.png" alt="Plan layout" />
                            <div class="image-label">Blueprint of an API we were cooking up while brainstorming.</div>
                        </div>
                    </div>
                    <div class="section">
                        <h2 class="text_center">Building Version One of the Website</h2>
                        <hr />
                        <p>
                            We designed our website to operate like this: When you submit a poem, our server automatically enters it into a MySQL database. When a library visitor taps a button to print a poem, the Raspberry Pi sends an HTTP
                            request to our server to retrieve a poem from the database. Our server responds with a poem, which the Raspberry Pi forwards directly to the thermal printer. Finally, the visitor receives a freshly printed
                            receipt with a poem.
                        </p>
                        <p>Our website was built off of a design template made by one of Coding Club members. We integrated it seamlessly with our build and we think it turned out pretty well!</p>
                        <div class="image-container center_grid_container">
                            <img src="./images/first_step.png" alt="Author Information Screen" />
                            <div class="image-label">#1) First page you see upon opening the website. Here you can enter your name and email and continue to the next step.</div>
                        </div>
                        <div class="image-container center_grid_container">
                            <img src="./images/second_step.png" alt="Write Your Poem" />
                            <div class="image-label">#2) Page you see after enter your name and email. Here you can start writing your poem!</div>
                        </div>
                        <div class="image-container center_grid_container">
                            <img src="./images/third_step.png" alt="Sent Screen" />
                            <div class="image-label">#3) Screen after submitting your poem. You recieve an ID to track your poem for later!</div>
                        </div>
                    </div>
                    <div class="section">
                        <h2 class="text_center">Struggles</h2>
                        <hr />
                        <h3>Moderation/Security</h3>
                        <p>
                            A group of trolls/internet gangsters caught wind of the project. They spammed our site with less-than-appropriate poems and with no security measures this presented a very critical threat. Could anyone just
                            submit anything they want? Obviously, we couldn’t allow this so we developed a moderation system. Each poem that’s submitted needs to be reviewed by a moderator before it can ever touch the printer.
                        </p>
                        <div class="image-container center_grid_container">
                            <img src="./images/old_approval_page.png" alt="Approval Page" />
                            <div class="image-label">The approval site we made to address the inappropriate poem problem.</div>
                        </div>
                        <h3>Mechanical/Hardware Issues With the Printer Itself</h3>
                        <p>
                            After finishing the website and API, we began toying around with the printer, trying different fonts and font sizes, trying to print images, etc, but we could never get the ink to read clearly. Even after
                            adjusting some settings through the Adafruit Printer API, we were never able to get a crisp image. After doing some research and watching a few videos, we found the culprit was the power adapter. We upgraded it
                            and it fixed the issue.
                        </p>
                    </div>
                    <div class="section">
                        <h2 class="text_center">Presentation Day</h2>
                        <hr />
                        <p>
                            After weeks of programming, bug fixing, and careful tweaking, we finally finished the project and were ready to present it to the library staff! Soon later Mr. Ketcham and Ms. Tobin made a visit to the Coding
                            Club, and we unveiled our weeks of hard work and they loved it! It was working wonderfully and they were ecstatic to see the idea come to fruition!
                        </p>
                        <p>
                            A few days later, we visited the library to meet their IT department so we could move the project from our servers to theirs. The process went smoothly. We downloaded our code base from GitHub to their server,
                            set up the MySQL database through their phpMyAdmin database, and everything was seemingly working without any issues.
                        </p>
                    </div>
                    <div class="section">
                        <h2 class="text_center">Roadblock</h2>
                        <hr />
                        <p>
                            A year passed by. And nothing. The project was nowhere to be found and it seemed like the project was dead in its tracks. Lost to sit dormant forever until forgotten completely. We could not let that be the case
                            forever.
                        </p>
                        <h3>What happened?</h3>
                        <p>Shortly after getting our project online, the library website began to get some renovations. During the update process, our project unfortunately got corrupted as well as some potential security issues raised.</p>
                    </div>
                    <div class="section">
                        <h2 class="text_center">Poem Collector 2.0 Arrives</h2>
                        <hr />
                        <p>
                            We realized we had built an incomplete project. Full of buttons with no destination, labels with missing features, and security vulnerabilities. The terms of service button led nowhere, you were given an ID to
                            track your poems for status updates but no such system existed, and passwords were stored in plain text — one of which even leaked on our GitHub portal! We definitely weren’t professionals. Some things were
                            definitely done sloppy. But we were going to do this right.
                        </p>
                        <h3>New Features / Changes</h3>
                        <p>The project got a massive upgrade:</p>
                        <ul>
                            <li>
                                <strong>Styling</strong> - instead of a blank textbox for the poem submission page, we thought it would be more creative if it instead mimicked the style of a receipt itself. This way you can see how your
                                poem will actually look after its printed.
                            </li>
                            <li><strong>Saving functionality</strong> - before after you sent your poem in, you'd never see it again. Now you’re not only able to see it again but keep yourself posted on all its status updates!</li>
                            <li><strong>Commenting functionality</strong> - we thought it would be cool if people could leave positive comments for authors. You can leave a comment by scanning the QR code at the bottom of any poem!</li>
                            <li><strong>Improved Printer</strong> - the old printer was literally just a mess of electronics all garbled together. Now the printer is much more compact and streamlined!</li>
                            <li><strong>Improved Security</strong> - All deprecated functions were removed, passwords are now encrypted, captcha was integrated to protect against spam, and much more!</li>
                            <li>
                                <strong>Improved Moderation Portal</strong> - the old portal for reviewing poems was less than preferable. The UI interface was a little bit ugly and definitely didn’t allow any room for mistakes —
                                accidentally approve a poem that shouldn’t be approved and there is no way to get that poem removed. With the new interface, that shouldn’t be a problem at all thanks to its search and menu bar to make
                                navigating the database significantly easier!
                            </li>
                            <li><strong>Improved User Moderation Account Control</strong> - before only one account can act as a moderator and review poems. Now anyone can be a moderator. Moderators can even sign up other moderators.</li>
                        </ul>
                        <p>
                            These are just the main ones and hopefully the future lies for much more! We’re glad to announce as well that our project is open source which means anyone can contribute to the project or download it for
                            themselves by accessing our <a href="https://github.com/Caeden01/West-Bloomfield-Library-Poem-Collector" style="color: orange;"><b>GitHub!</b></a>
                        </p>
                        <div class="image-container center_grid_container">
                            <img src="./images/printer.png" alt="Printer" />
                            <div class="image-label">Photo of the thermal printer.</div>
                        </div>
                    </div>
                    <div class="section">
                        <h2 class="text_center">Credits</h2>
                        <hr />
                        <p>A big thanks to everyone who helped with this project!!</p>
                        <div class="credit-item">
                            <img src="./images/stephen_ketcham.jpg" alt="Stephen Ketcham" />
                            <div class="credit-details"><strong>Stephen Ketcham:</strong> Came up with the idea, supplied coding club the resources to make the project, and provided Coding Club such an amazing project to work on!</div>
                        </div>
                        <div class="credit-item">
                            <img src="./images/james_shaw.jpg" alt="James Shaw" />
                            <div class="credit-details">
                                <strong>James Shaw:</strong> Hosted Coding Club in his room Tuesdays after school allowing each of us to be able to work on this project. Handled a lot of the communication and let us work on the project
                                sometimes during class!
                            </div>
                        </div>
                        <div class="credit-item">
                            <img src="./images/caeden_kidd.jpg" alt="Caeden Kidd" />
                            <div class="credit-details">
                                <strong>Caeden Kidd:</strong> Led the development for the project. Wrote the poem collector API for both the server side backend and the printer. Designed the style and functionality for the front-end
                                including most of the CSS and Javascript. Inspired version two of the project.
                            </div>
                        </div>
                        <div class="credit-item">
                            <img src="./images/nate_pongtankul.jpg" alt="Nate Pongtankul" />
                            <div class="credit-details"><strong>Nate Pongtankul:</strong> Made important revisions to code to improve readability and improve efficiency. His edits are public on the <a href="https://github.com/Caeden01/West-Bloomfield-Library-Poem-Collector" style="color: orange;"><b>GitHub</b></a> repository.</div>
                        </div>
                        <div class="credit-item">
                            <img src="./images/max_gorman.jpg" alt="Max Gorman" />
                            <div class="credit-details"><strong>Max Gorman:</strong> Wrote code and designed a digital print button for the first printer.</div>
                        </div>
                        <div class="credit-item">
                            <img src="./images/ryan_sparago.jpg" alt="Ryan Sparago" />
                            <div class="credit-details"><strong>Ryan Sparago:</strong> Contributed to the frontend design and team morale.</div>
                        </div>
                        <div class="credit-item">
                            <img src="./images/ari_micznik.jpg" alt="Ari Micznik" />
                            <div class="credit-details"><strong>Ari Micznik:</strong> Working on new features currently in progress and designed the contact page.</div>
                        </div>
                        <div class="credit-item">
                            <img src="./images/eesh_garg.jpg" alt="Eesh Garg" />
                            <div class="credit-details"><strong>Eesh Garg:</strong> Recruited team members and supported project coordination.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="window_container page_3 animate__animated">
                <div class="container slightly_small_wide">
                    <div class="section">
                        <h1>Poem System FAQs</h1>
                        <hr />

                        <h2>How Does the Poem ID System Work?</h2>
                        <p>
                            Each poem is assigned a unique ID to distinguish it from others. After submission, you receive this ID. You can either save it to your profile under
                            <a href="#my-poems" style="color: orange;"><b>My Poems</b></a> for easy retrieval or write it down.
                        </p>

                        <h2>What Should I Do If I Lost My Poem ID?</h2>
                        <p>
                            If you lose your Poem ID and did not save it, contact a moderator through the <a href="#contact" style="color: orange;"><b>contact page</b></a>. Provide your name and the poem's title, and they may assist in
                            retrieving it.
                        </p>

                        <h2>Comment System</h2>
                        <ul>
                            <li><strong>Who Can Leave Comments?</strong> Only moderators and library readers can leave comments. This feature aims to connect both writers and readers.</li>
                            <li><strong>Replying to Comments</strong>: Direct replies are not possible. Comments can only be addressed if the commenter provided their contact information which isn't always the case.</li>
                            <li>
                                <strong>Handling Insulting Comments</strong>: Report any negative or hateful comments to us through our <a href="#contact" style="color: orange;"><b>contact page</b></a>. We investigate and may ban users
                                engaging in such behavior.
                            </li>
                        </ul>

                        <h2>Poem Approval and Rejection</h2>
                        <ul>
                            <li><strong>Approval Time</strong>: Approval depends on moderator availability.</li>
                            <li>
                                <strong>If Rejected</strong>: Rejection may be due to inappropriate content or other issues. Contact a moderator through the <a href="#contact" style="color: orange;"><b>contact page</b></a> to discuss
                                possible re-evaluation.
                            </li>
                        </ul>

                        <h2>Reporting Inappropriate Content</h2>
                        <p>
                            If you encounter an inappropriate poem, report it through the <a href="#contact" style="color: orange;"><b>contact page</b></a> for review.
                        </p>

                        <h2>Status Updates</h2>
                        <p>
                            Currently, we do not offer notifications. Check the <a href="#my-poems" style="color: orange;"><b>My Poems</b></a> section on our website for updates on your submissions.
                        </p>

                        <h2>Website Issues</h2>
                        <p>
                            If the website isn't working, please visit the <a href="#contact" style="color: orange;"><b>contact page</b></a> for assistance.
                        </p>

                        <h2>Getting Involved</h2>
                        <ul>
                            <li><strong>Spread the Word</strong>: Encourage others to submit poems.</li>
                            <li><strong>Contribute to Development</strong>: Help improve features via our <a href="https://github.com/Caeden01/West-Bloomfield-Library-Poem-Collector" style="color: orange;"><b>GitHub</b></a> repository. We welcome contributions and suggestions.</li>
                        </ul>

                        <h2>Setting Up for Your Library</h2>
                        <p>
                            If you're interested in setting up a similar system at your library, contact the Coding Club at West Bloomfield High School via the <a href="#contact" style="color: orange;"><b>contact page</b></a> to discuss
                            potential arrangements. Or visit our <a href="https://github.com/Caeden01/West-Bloomfield-Library-Poem-Collector" style="color: orange;"><b>GitHub</b></a> page where we provide a step by step tutorial on how to set up and configure this very site.
                        </p>
                    </div>
                </div>
            </div>
            <div class="window_container page_4 animate__animated">
                <div class="container just_padding" id="contact_container">
                    <form action="" id="contact_form" style="display: block;">
                        <div class="context_info medium_max_width_container">
                            <h1>Contact</h1>
                            Welcome to our contact page. Please describe the nature of your issue / request and we'll get back to you through email.
                        </div>
                        <div class="label">Name <span class="required">*</span></div>
                        <input type="text" id="contact_name" class="generic_input" style="max-width: 400px;" spellcheck="false" required maxlength="100" />
                        <div class="label">Email <span class="required">*</span></div>
                        <input type="email" id="contact_email" class="generic_input" style="max-width: 400px;" maxlength="100" />
                        <div class="label">Subject <span class="required">*</span></div>
                        <select class="generic_input" id="contact_issue" name="subject" style="max-width: 400px;" required>
                            <option value="" selected disabled>Select subject...</option>
                            <option value="Terms of Service">Terms of Service</option>
                            <option value="Technical Issue(s)">Technical Issue(s)</option>
                            <option value="I Lost My Poem">My Poem is Missing</option>
                            <option value="My Poem Was Rejected">My Poem Was Rejected</option>
                            <option value="A Poem I Recieved Is Inappropriate">A Poem I Recieved is Inappropriate</option>
                            <option value="A Comment on My Poem is Inappropriate">A Comment on My Poem is Inappropriate</option>
                            <option value="I Want to Delete My Poem">I Want to Delete My Poem</option>
                            <option value="I Want to be a Moderator">I Want To Be a Moderator</option>
                            <option value="I would like this system for my library">I would like this for my library</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="label">Issue / Request <span class="required">*</span></div>
                        <textarea class="display_textarea" id="contact_response" maxlength="1000"></textarea>
                        <button type="submit" class="large_orange_button generic_input" style="max-width: 470px;">Submit</button>
                    </form>
                </div>
                <div class="container just_padding animate__animated" id="post_contact_success" style="display: none; max-width: 400px;">
                    <div class="flexbox">
                        <img src="./images/checkmark.png" style="width: 125px; float: left; margin-right: 10px;" />
                        <div>
                            Thank you, <span id="contact_submit_name">*NAME*</span>. Your response has been successfully submitted to our servers and will be reviewed soon. Your ticket ID is:
                            <b><span id="ticket_id" style="color: orange;">*ID*</span></b>.
                        </div>
                    </div>
                </div>
            </div>
            <div class="window_container page_5 animate__animated">
                <div class="fixed_width_padding container animate__animated" id="login_container" style="display: block;">
                    <h3>Welcome to the West Bloomfield Library Poem Approver Portal!</h3>
                    <p>You need to be registered in order to access this page.</p>
                    <form>
                        <div class="label">Username <span class="required">*</span></div>
                        <input type="text" class="generic_input" spellcheck="false" required id="login_name" />
                        <div class="label">Password <span class="required">*</span></div>
                        <input type="password" class="generic_input" spellcheck="false" required id="password" />
                        <button type="submit" class="generic_input large_orange_button" id="login">Login</button>
                    </form>
                </div>
                <div class="container just_padding medium_max_width_container animate__animated" id="post_login" style="display: none;">
                    <h1>Welcome **Name**</h1>
                    <p>As a <span id="tier_status">*BLANK*</span> tier moderator you are able to access the following pages. Have fun!</p>
                    <a href="#poem-swipper"><button class="side_button admin_feature admin_feature_0 darker">Poem Swiper</button></a>
                    <a href="#database"><button class="side_button admin_feature admin_feature_1 darker">Poem Database</button></a>
                    <a href="#issues"><button class="side_button admin_feature admin_feature_2 darker">View Reported Issues</button></a>
                    <a href="#users"><button class="side_button admin_feature admin_feature_3 darker">Manage Moderators</button></a>
                    <a href="#print-queue"><button class="side_button admin_feature admin_feature_4 darker">Print Queue</button></a>
                    <button class="side_button darker" onclick="logout()">Sign out</button>
                </div>
            </div>
            <div class="window_container page_6 animate__animated">
                <div class="approve_container animate__animated"></div>
            </div>
            <div class="window_container page_8 animate__animated">
                <div class="container doesnt_get_small_but_not_too_big">
                    <div class="center_grid_container">
                        <h1>Poem Database Portal</h1>
                    </div>
                    <div class="section_container">
                        <button class="option" id="pending_btn" onclick="selectDatabase('Pending', true)">Pending</button>
                        <button class="option" id="approved_btn" onclick="selectDatabase('Approved', true)">Approved</button>
                        <button class="option" id="rejected_btn" onclick="selectDatabase('Rejected', true)">Rejected</button>
                        <button class="option_fake" onclick="searchDatabase()"><b class="symbols_font">🔍</b></button>
                    </div>
                    <div class="center_grid_container">
                        <div id="poem_list_container" class="flexbox" style="flex-wrap: wrap; display: flex; text-align: left; transition: 0.4s ease opacity;"></div>
                    </div>
                </div>
            </div>
            <div class="window_container page_9 animate__animated">
                <div class="container just_padding center_grid_container">
                    <div class="medium_max_width_container">
                        <h1>Reported Issues Portal</h1>
                        <p>Below you can see the issues / requests people have submitted via Contact Moderators.</p>
                        <div class="section_container">
                            <button class="option" id="open_btn" onclick="loadIssues('open')">Opened</button>
                            <button class="option" id="closed_btn" onclick="loadIssues('closed')">Closed</button>
                        </div>
                    </div>
                    <div id="issues" class="table_container"></div>
                </div>
            </div>
            <div class="window_container page_10 animate__animated">
                <div class="container slightly_small_wide center_grid_container">
                    <div class="medium_max_width_container">
                        <h1>Manage Moderator Portal</h1>
                        <p>Below you can see the all the moderators registered on this website, add a new moderator, and regulate existing accounts.</p>
                    </div>
                    <div class="create_account_box">
                        <div class="create_account_form">
                            <input type="hidden" name="action" value="create_account" />
                            <label for="username">New Account Username:</label>
                            <input type="text" id="account_create_username" class="generic_input" maxlength="25" required />
                            <label for="tierDropdown">Account Privileges:</label>
                            <select id="tierDropdown" name="options">
                                <option value="low">Low tier</option>
                                <option value="medium">Mid tier</option>
                                <option value="high">High tier</option>
                            </select>
                            <button type="submit" class="generic_input" id="create_account">Create Account</button>
                        </div>
                    </div>
                    <div class="user_info_container"></div>
                </div>
            </div>
            <div class="window_container page_11 animate__animated">
                <div class="container just_padding not_expandable center_grid_container">
                    <div class="medium_max_width_container">
                        <h1>Print Queue</h1>
                        <p>Below is a list of the poems that will print in order before the printer selects randomly from the database.</p>
                    </div>
                    <div id="queueList"></div>
                </div>
            </div>
        </div>
        <div class="footer">
            Made by <a target="_blank" rel="noopener noreferrer" href="https://www.linkedin.com/in/caeden-kidd-0957a1246/" style="color: orange;"><b>Caeden Kidd</b></a>,
            <a target="_blank" rel="noopener noreferrer" href="https://github.com/Player01osu" style="color: orange;"><b>Nate Pongtankul</b></a>,
            <a href="https://www.linkedin.com/in/eesh-garg-3a5456284" target="_blank" rel="noopener noreferrer" style="color: orange;"><b>Eesh Garg</b></a>,
            <a href="https://www.linkedin.com/in/max-gorman" target="_blank" rel="noopener noreferrer" style="color: orange;"><b>Max Gorman</b></a>,
            <a href="https://www.linkedin.com/in/ryan-sparago-b38244291" target="_blank" rel="noopener noreferrer" style="color: orange;"><b>Ryan Sparago</b></a>, and Ari Micznik from the Coding Club at West Bloomfield High School.
        </div>
        <script src="./scripts/main_script.js"></script>
    </body>
</html>
