function editKeywords(courseId, activityId, keywords) {
        // Display a form to edit keywords
        var editedKeywords = prompt("Edit Keywords:", keywords);
        if (editedKeywords !== null && editedKeywords !== "") {
            // Use AJAX to update keywords without page reload
            $.ajax({
                url: "view.php", // Same page URL
                type: "POST",
                data: {
                    edit_keywords: 1,
                    courseid: courseId,
                    activityid: activityId,
                    edited_keywords: editedKeywords
                },
                success: function(response) {
                    // Handle success (e.g., refresh the page with the correct course id)
                    window.location.href = "view.php?courseid=" + courseId;
                },
                error: function(error) {
                    // Handle error
                    console.error("Error editing keywords: " + error);
                }
            });
        } else if (editedKeywords === "") {
            // Handle the case where the user entered an empty string
            alert("Keywords cannot be empty.");
        }
    }

    function deleteKeywords(entryId, courseId) {
        if (confirm("Are you sure you want to delete this entry?")) {
            
            $.ajax({
                url: "delete_keywords.php", // Replace with the actual delete script URL
                type: "POST",
                data: { entryId: entryId, courseId: courseId },
                success: function(response) {
                    // Handle success (e.g., refresh the page)
                    window.location.href = "view.php?courseid=" + courseId;
                },
                error: function(error) {
                    // Handle error
                    console.error("Error deleting entry: " + error);
                }
            });
        }
    }

    function toggleKeywords(activityId) {
        var keywordsElement = document.getElementById("keywords_" + activityId);
        var editDeleteButtons = document.getElementsByClassName("edit-delete-buttons_" + activityId);
        var arrowElement = keywordsElement.previousElementSibling.querySelector(".toggle-arrow");
    
        if (keywordsElement.style.display === "none" || keywordsElement.style.display === "") {
            keywordsElement.style.display = "block";
            for (var i = 0; i < editDeleteButtons.length; i++) {
                editDeleteButtons[i].style.display = "inline-block";
            }
            arrowElement.innerHTML = "&#x25BC;"; // Arrow down
        } else {
            keywordsElement.style.display = "none";
            for (var i = 0; i < editDeleteButtons.length; i++) {
                editDeleteButtons[i].style.display = "none";
            }
            arrowElement.innerHTML = "&#x25B6;"; // Arrow to the right
        }
    }

    // Show keywords and buttons on page load
    document.addEventListener("DOMContentLoaded", function() {
        var allKeywordElements = document.querySelectorAll("[id^=\'keywords_\']");
        var allButtonsElements = document.querySelectorAll("[class^=\'edit-delete-buttons_\']");

        for (var i = 0; i < allKeywordElements.length; i++) {
            allKeywordElements[i].style.display = "block";
        }

        for (var i = 0; i < allButtonsElements.length; i++) {
            allButtonsElements[i].style.display = "inline-block";
        }
    });

    function toggleKeywordsNew(activityId, action) {
        var keywordsElements = document.querySelectorAll("[id^=\'keywords_\']");
        var editDeleteButtons = document.querySelectorAll("[class^=\'edit-delete-buttons_\']");
        var expandAllButton = document.getElementById("expandAllButton");
        var collapseAllButton = document.getElementById("collapseAllButton");
        var courseSummaryKeywordsElement = document.getElementById("courseSummaryKeywords");
    
        // Check if there are any input keywords on the page
        var inputKeywordsExist = document.querySelectorAll(".keyword-group").length > 0;
    
        for (var i = 0; i < keywordsElements.length; i++) {
            var keywordsElement = keywordsElements[i];
            var editDeleteButton = editDeleteButtons[i];
            var arrowElement = keywordsElement.previousElementSibling.querySelector(".toggle-arrow");
    
            if (action === "collapse") {
                keywordsElement.style.display = "none";
                for (var j = 0; j < editDeleteButtons.length; j++) {
                    editDeleteButtons[j].style.display = "none";
                }
                arrowElement.innerHTML = "&#x25B6;"; // Arrow to the right
                expandAllButton.style.display = inputKeywordsExist ? "block" : "none";
                collapseAllButton.style.display = "none";
            } else {
                keywordsElement.style.display = "block";
                for (var j = 0; j < editDeleteButtons.length; j++) {
                    editDeleteButtons[j].style.display = "inline-block";
                }
                arrowElement.innerHTML = "&#x25BC;"; // Arrow down
                expandAllButton.style.display = "none";
                collapseAllButton.style.display = inputKeywordsExist ? "block" : "none";
            }
        }
    
        // Handle course summary keywords separately
        if (courseSummaryKeywordsElement) {
            if (action === "collapse") {
                courseSummaryKeywordsElement.classList.remove("expanded");
                // Hide all links and borders
                var linksAndBorders = courseSummaryKeywordsElement.querySelectorAll(".keyword-group a, .keyword-group");
                linksAndBorders.forEach(function(linkOrBorder) {
                    linkOrBorder.style.display = "none";
                });
                // Update the collapseAllButton display
                collapseAllButton.style.display = "none";
                expandAllButton.style.display = inputKeywordsExist ? "block" : "none";
            } else {
                courseSummaryKeywordsElement.classList.add("expanded");
                // Show all links and borders
                var linksAndBorders = courseSummaryKeywordsElement.querySelectorAll(".keyword-group a, .keyword-group");
                linksAndBorders.forEach(function(linkOrBorder) {
                    linkOrBorder.style.display = "block";
                });
                // Update the collapseAllButton display
                collapseAllButton.style.display = "block";
                expandAllButton.style.display = "none";
            }
    
            var summaryKeywordArrows = courseSummaryKeywordsElement.querySelectorAll(".toggle-arrow");
            summaryKeywordArrows.forEach(function(arrow) {
                arrow.innerHTML = action === "collapse" ? "&#x25B6; " + arrow.innerHTML.substring(2) : "&#x25BC; " + arrow.innerHTML.substring(2);
            });
        }
    
        // Show or hide links under each keyword based on action
        keywordsElements.forEach(function(keywordsElement) {
            var keywordGroups = keywordsElement.querySelectorAll(".keyword-group");
            keywordGroups.forEach(function(keywordGroup) {
                var links = keywordGroup.querySelectorAll("a");
                if (action === "collapse") {
                    keywordGroup.style.display = "none";
                    links.forEach(function(link) {
                        link.style.display = "none";
                    });
                } else {
                    keywordGroup.style.display = "block";
                    links.forEach(function(link) {
                        link.style.display = "block";
                    });
                }
            });
    
            // Update the arrow state next to each keyword
            var keywordArrows = keywordsElement.querySelectorAll(".toggle-arrow");
            keywordArrows.forEach(function(keywordArrow) {
                keywordArrow.innerHTML = action === "collapse" ? "&#x25B6; " + keywordArrow.innerHTML.substring(2) : "&#x25BC; " + keywordArrow.innerHTML.substring(2);
            });
        });
    }
    
    
// Function to display keywords and buttons when loading the page
    document.addEventListener("DOMContentLoaded", function() {
        var allKeywordElements = document.querySelectorAll("[id^=\'keywords_\']");
        var allButtonsElements = document.querySelectorAll("[class^=\'edit-delete-buttons_\']");
        var courseSummaryKeywordsElement = document.getElementById("courseSummaryKeywords");
    
        for (var i = 0; i < allKeywordElements.length; i++) {
            allKeywordElements[i].style.display = "block";
        }
    
        for (var i = 0; i < allButtonsElements.length; i++) {
            allButtonsElements[i].style.display = "inline-block";
        }
    
// Execute "Collapse All" on page load
        toggleKeywordsNew(0, "collapse");
    
// Additionally for course summary keywords
var courseSummaryKeywordsElement = document.getElementById("courseSummaryKeywords");
        if (courseSummaryKeywordsElement) {
            courseSummaryKeywordsElement.style.display = "block";
            courseSummaryKeywordsElement.classList.add("expanded"); 
        }
    });
    function toggleCourseSummaryKeyword(arrowElement) {
    var keywordGroup = arrowElement.nextElementSibling; //Next sibling node is the keyword group
    var linksAndBorders = keywordGroup.querySelectorAll(".keyword-group a, .keyword-group");

    if (keywordGroup.style.display === "none" || keywordGroup.style.display === "") {
        keywordGroup.style.display = "block";
        arrowElement.innerHTML = "&#x25BC; " + arrowElement.innerHTML.substring(2); 

// Show both links and borders
        linksAndBorders.forEach(function (linkOrBorder) {
            linkOrBorder.style.display = "block";
        });
    } else {
        keywordGroup.style.display = "none";
        arrowElement.innerHTML = "&#x25B6; " + arrowElement.innerHTML.substring(2); 

// hide both links and borders
        linksAndBorders.forEach(function (linkOrBorder) {
            linkOrBorder.style.display = "none";
        });
    }
}