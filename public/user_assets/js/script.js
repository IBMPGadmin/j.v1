$(function () {
  console.log("Script loaded");

  $(".view-mode-btn").on("click", function () {
    $(".view-mode-btn").removeClass("active-view");
    $(this).addClass("active-view");
  });

  $(".view-mode-btn").on("click", function () {
    // Remove active class from all buttons
    $(".view-mode-btn").removeClass("active");

    // Add active class to the clicked button
    $(this).addClass("active");

    // Toggle between Grid and List views
    const viewMode = $(this).data("view-mode");
    $(".grid-view").css("display", viewMode === "grid" ? "grid" : "none");
    $(".list-view").css("display", viewMode === "list" ? "flex" : "none");
  });

  var popupZIndex = 10;

  var activePopups = {}; // Store section-category key with a count

  function canOpenPopup(popupKey) {
    return !activePopups[popupKey] || activePopups[popupKey] < 10;
  }

  function makeDraggable(popup) {
    $(popup).draggable({
      handle: ".popup-title",
      containment: "window",
      start: function () {
        $(this).css("z-index", ++popupZIndex);
      },
    });
  }

  $(".nested-droppable").droppable({
    accept: ".popup",
    hoverClass: "ui-state-highlight",
    drop: function (event, ui) {
      var $this = $(this);
      if (!ui.helper.hasClass("pinned")) {
        $this.append(ui.helper);
        ui.helper
          .css({
            position: "relative",
            top: "auto",
            left: "auto",
            width: "100%",
          })
          .addClass("pinned");
        ui.helper.draggable("option", "disabled", true);
      }
      $this
        .sortable({
          items: ".popup",
          handle: ".popup-title",
          placeholder: "ui-state-highlight",
          forcePlaceholderSize: true,
          update: function () {
            ui.item.css({
              width: "100%",
              position: "relative",
              top: "auto",
              left: "auto",
            });
            console.log("Popup order updated.");
          },
        })
        .sortable("refresh");
    },
  });

  // TinyMCE initialization
  tinymce.init({
    selector: "#tiny-editor",
    height: 380,
    menubar: false,
    plugins: [
      "advlist",
      "autolink",
      "lists",
      "link",
      "image",
      "charmap",
      "preview",
      "searchreplace",
      "visualblocks",
      "code",
      "fullscreen",
      "insertdatetime",
      "table",
      "help",
      "wordcount",
    ],
    toolbar:
      "copy savedb loaddb | undo redo | formatselect | " +
      "bold italic underline forecolor backcolor | alignleft aligncenter " +
      "alignright alignjustify | bullist numlist outdent indent | " +
      "lineheight | removeformat | help",
    lineheight_formats: "1 1.2 1.5 2 2.5 3",
    content_style:
      "body { font-family:Helvetica,Arial,sans-serif; font-size:14px; }",
    branding: false,
    statusbar: false,
    setup: function (editor) {
      // Add custom toolbar buttons
      editor.ui.registry.addIcon(
        "copy-content",
        '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M16 1H4C2.9 1 2 1.9 2 3V17H4V3H16V1ZM19 5H8C6.9 5 6 5.9 6 7V21C6 22.1 6.9 23 8 23H19C20.1 23 21 22.1 21 21V7C21 5.9 20.1 5 19 5ZM19 21H8V7H19V21Z"/></svg>'
      );
      editor.ui.registry.addIcon(
        "save-db",
        '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M19 12v7H5v-7H3v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2zm-6 .67l2.59-2.58L17 11.5l-5 5-5-5 1.41-1.41L11 12.67V3h2z"/></svg>'
      );
      editor.ui.registry.addIcon(
        "load-db",
        '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M19 12v7H5v-7H3v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2zm-6-7.67l2.59 2.58L17 8.5l-5-5-5 5 1.41-1.41L11 7.33V17h2z"/></svg>'
      );

      editor.ui.registry.addButton("copy", {
        icon: "copy-content",
        tooltip: "Copy all content",
        onAction: function () {
          const content = editor.getContent();
          const $temp = $("<div>")
            .css({
              position: "absolute",
              left: "-9999px",
              top: "0",
            })
            .html(content)
            .appendTo("body");

          try {
            document.execCommand("copy");
            showSuccessMessage("Content copied!");
          } catch (err) {
            handleError(err);
          } finally {
            $temp.remove();
          }
        },
      });

      editor.ui.registry.addButton("savedb", {
        icon: "save-db",
        tooltip: "Save to database",
        onAction: function () {
          const content = editor.getContent();

          $.post(BASE_URL + "/jurislocator_version_2/user/api/save_text.php", {
            edited_content: content,
            category_id: 1, // Update this dynamically if needed
            client_id: window.selectedClientId, // Ensure client selection is used
          })
            .done(function (response) {
              console.log("RAW Response from save_text.php:", response);

              if (typeof response === "string") {
                try {
                  response = JSON.parse(response);
                } catch (error) {
                  console.error("JSON Parsing Error:", error, response);
                  alert("Invalid JSON response from the server.");
                  return;
                }
              }

              if (response.success) {
                showSuccessMessage("Content saved to database!");
              } else {
                console.error("Server error:", response.message);
                alert("Failed to save content.");
              }
            })
            .fail(function (xhr, status, error) {
              console.error("AJAX error:", xhr.status, error);
              alert("Error: " + xhr.status + " " + error);
            });
        },
      });

      editor.ui.registry.addButton("loaddb", {
        icon: "load-db",
        tooltip: "Load latest from database",
        onAction: function () {
          $.get(BASE_URL + "/jurislocator_version_2/user/api/get_text.php", {
            category_id: 1, // Update dynamically
            client_id: window.selectedClientId,
          })
            .done(function (response) {
              console.log("RAW Response from get_text.php:", response);

              if (typeof response === "string") {
                try {
                  response = JSON.parse(response);
                } catch (error) {
                  console.error("JSON Parsing Error:", error, response);
                  alert("Invalid JSON response from the server.");
                  return;
                }
              }

              if (response.success && response.content) {
                editor.setContent(response.content);
                localStorage.setItem("tinymce-content", response.content);
                showSuccessMessage("Content loaded from database!");
              } else {
                console.error("Server error:", response.message);
                alert("No content found.");
              }
            })
            .fail(function (xhr, status, error) {
              console.error("AJAX error:", xhr.status, error);
              alert("Error: " + xhr.status + " " + error);
            });
        },
      });

      // Initialize with blank content
      editor.on("init", function () {
        // Check if a client is selected
        const currentClient = document.getElementById("current-client");

        if (currentClient && currentClient.textContent.trim() === "None") {
          // No client selected, start with empty editor
          editor.setContent("");
          localStorage.removeItem("tinymce-content");
        } else {
          // Client selected, attempt to load from localStorage as fallback
          const savedContent = localStorage.getItem("tinymce-content");
          if (savedContent) {
            editor.setContent(savedContent);
          }

          // Try to load latest content for selected client
          if (window.selectedClientId) {
            $.get(BASE_URL + "/jurislocator_version_2/user/api/get_text.php", {
              category_id: 1,
              client_id: window.selectedClientId,
            }).done(function (response) {
              if (typeof response === "string") {
                try {
                  response = JSON.parse(response);
                } catch (error) {
                  return;
                }
              }

              if (response.success && response.content) {
                editor.setContent(response.content);
                localStorage.setItem("tinymce-content", response.content);
              }
            });
          }
        }
      });

      // Save to localStorage on change
      editor.on("change", function () {
        localStorage.setItem("tinymce-content", editor.getContent());
      });
    },
  });

  // Centralized configuration for popup management
  const PopupManager = {
    popupZIndex: 10,
    activePopups: {}, // Store section-category key with a count
    maxPopupsPerKey: 10,

    canOpenPopup: function (popupKey) {
      return (
        !this.activePopups[popupKey] ||
        this.activePopups[popupKey] < this.maxPopupsPerKey
      );
    },

    incrementPopupCount: function (popupKey) {
      this.activePopups[popupKey] = (this.activePopups[popupKey] || 0) + 1;
    },

    decrementPopupCount: function (popupKey) {
      if (this.activePopups[popupKey]) {
        this.activePopups[popupKey]--;
        if (this.activePopups[popupKey] <= 0) {
          delete this.activePopups[popupKey];
        }
      }
    },
  };

  function extractPart(titleText) {
    let match = titleText.match(/Part (\d+)/i);
    return match ? match[1] : "";
  }

  function extractDivision(titleText) {
    let match = titleText.match(/Division (\d+(\.\d+)?)/i);
    return match ? match[1] : "";
  }

  function savePinnedPopups() {
    const pinnedPopups = $(".nested-droppable .popup")
      .map(function () {
        let sectionId = $(this).attr("data-section-id");
        let categoryId = $(this).attr("data-category-id");
        let clientId = window.selectedClientId || 0;

        // Extract part and division from the popup title
        let titleText = $(this).find(".popup-title .title-text").text().trim();
        let extractedPart = extractPart(titleText);
        let extractedDivision = extractDivision(titleText);

        console.log(
          "Popup Data - Section ID:",
          sectionId,
          "Category ID:",
          categoryId,
          "Client ID:",
          clientId,
          "Part:",
          extractedPart,
          "Division:",
          extractedDivision
        );

        if (!sectionId || !categoryId) {
          console.warn("Skipping popup due to missing data:", this);
          return null; // Skip if data is missing
        }

        return {
          section_id: sectionId,
          category_id: parseInt(categoryId, 10),
          client_id: clientId,
          part: extractedPart !== "" ? extractedPart : null,
          division: extractedDivision !== "" ? extractedDivision : null,
        };
      })
      .get()
      .filter((popup) => popup !== null);

    console.log(
      "Final Data Sent to Server:",
      JSON.stringify(
        {
          action: "save",
          popups: pinnedPopups,
        },
        null,
        2
      )
    );

    $.ajax({
      url: BASE_URL + "/jurislocator_version_2/user/api/save_pinned_popups.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        action: "save",
        popups: pinnedPopups,
      }),
      success: function (response) {
        console.log("Server response:", response);
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Saved",
            text: `Saved ${response.saved_count} pinned popups`,
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
        } else {
          console.error("Save error:", response.message);
          alert("Failed to save pinned popups.");
        }
      },
      error: function (xhr, status, error) {
        console.error("Save failed:", xhr.responseText);
        alert("Network error while saving pinned popups.");
      },
    });
  }

  function fetchPinnedPopups() {
    if (!window.selectedClientId) {
      console.warn("No client selected. Skipping popup fetch.");
      return;
    }

    $.ajax({
      url: BASE_URL + "/jurislocator_version_2/user/api/save_pinned_popups.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        action: "fetch",
        client_id: window.selectedClientId,
      }),
      success: function (response) {
        console.log("Server Response:", response); // Debugging

        if (response.success) {
          $(".nested-droppable .popup").remove(); // Clear old pinned popups

          response.popups.forEach(function (popup) {
            console.log("Raw popup data from server:", popup);
            console.log("Popup part value:", popup.part);
            console.log("Popup division value:", popup.division);

            displayPinnedPopup(
              popup.section_id,
              popup.category_id,
              popup.part || null,
              popup.division || null
            );
          });

          // Initialize sortable functionality after loading all popups
          initializeSortable($(".nested-droppable"));

          Swal.fire({
            icon: "success",
            title: "Popups Loaded",
            text: `Loaded ${response.popups.length} pinned popups`,
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
        } else {
          console.error("Fetch error:", response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Fetch failed:", error);
        alert("Error fetching pinned popups.");
      },
    });
  }

  function displayPinnedPopup(sectionId, categoryId, part, division) {
    console.log(
      `displayPinnedPopup called with: section=${sectionId}, category=${categoryId}, part=${part}, division=${division}`
    );

    let sectionObj = {
      section_id: sectionId,
      part: part,
      division: division,
    };

    let titleText = getFullSectionIdentifier(sectionObj);
    console.log("Generated title text:", titleText);

    let popupHtml = `
            <div class="popup ui-draggable pinned" 
                data-section-id="${sectionId}" 
                data-category-id="${categoryId}" 
                data-part="${part || ""}" 
                data-division="${division || ""}"
                style="display: block; width: 100%; position: relative; top: auto; left: auto;">
                <div class="popup-title ui-draggable-handle">
                    <span class="title-text">${titleText}</span>
                    <span class="copy" title="Copy content"><i class="fas fa-copy"></i></span>
                    <span class="arrow">▼</span>
                    <span class="close">✖</span>
                </div>
                <div class="content-body" style="width: 100%; padding: 10px; max-height: 200px;">
                    <p class="popup-content">Loading...</p>
                </div>
            </div>`;

    console.log("Appending pinned popup to .nested-droppable:", popupHtml);
    let $popup = $(popupHtml).hide(); // Initially hide to prevent layout shift

    $(".nested-droppable").append($popup);

    // Ensure attributes are correctly assigned
    $popup.attr("data-part", part || "");
    $popup.attr("data-division", division || "");

    // Initialize popup functionality (draggable, closable, content loading)
    initializePopup($popup, sectionId, categoryId, part, division);

    // Make sure draggable is disabled for pinned popups
    $popup.draggable("option", "disabled", true);

    $popup.fadeIn(300);
  }

  // Updated to include part and division in the API call
  function initializePopup($popup, sectionId, categoryId, part, division) {
    // Make the popup draggable
    $popup.draggable({
      handle: ".popup-title",
      containment: "window",
      revert: "invalid", // Snap back if dropped outside
      start: function () {
        $(this).css("z-index", ++popupZIndex);
      },
    });

    // If popup is pinned, disable draggable
    if ($popup.hasClass("pinned")) {
      $popup.draggable("option", "disabled", true);
    }

    // Load popup content dynamically
    $.ajax({
      url: BASE_URL + '/api/section-content/' + categoryId + '/' + encodeURIComponent(sectionId),
      method: "GET",
      dataType: "json",
      success: function (response) {
        if (response.error) {
          console.error("Server Error:", response.error);
          $popup.find(".popup-content").html(`<p>Error loading content.</p>`);
        } else {
          // Success handling
          // Populate content and handle the title
          if (response.data && Array.isArray(response.data) && response.data.length > 0) {
            const section = response.data[0];
            $popup.find(".popup-title h2").text(section.title || "Section " + sectionId);
            $popup.find(".popup-content").html(section.text_content || section.content || "No content available");
          } else {
            $popup.find(".popup-content").html(`<p>No content found for this reference.</p>`);
          }
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        
        // Check if the response is JSON
        let errorMessage = "Error loading content.";
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.message) {
            errorMessage = response.message;
          }
        } catch (e) {
          // Not JSON, use generic message
          if (xhr.status === 401) {
            errorMessage = "You need to be logged in to view this content.";
          } else if (xhr.status === 404) {
            errorMessage = "Reference not found.";
          }
        }
        
        $popup.find(".popup-content").html(`<div class="alert alert-danger">${errorMessage}</div>`);
      },
    });

    // Setup UI elements (close, copy, toggle)
    setupPopupHandlers($popup);
  }

  // Function to get the full section identifier with part and division
  function getFullSectionIdentifier(data) {
    console.log("getFullSectionIdentifier received:", data);
    let identifier = "";

    if (data.part && data.part !== "null" && data.part !== "") {
      identifier += `Part ${data.part} `;
    }

    if (data.division && data.division !== "null" && data.division !== "") {
      identifier += `Division ${data.division} `;
    }

    if (data.section_id) {
      identifier += `Section ${data.section_id}`;
    }

    let result = identifier.trim();
    console.log("Generated identifier:", result);
    return result;
  }

  function setupPopupHandlers($popup) {
    $popup
      .find(".close")
      .off("click")
      .on("click", function () {
        $popup.fadeOut(200, function () {
          $(this).remove();
        });
      });

    $popup
      .find(".arrow")
      .off("click")
      .on("click", function () {
        const $content = $popup.find(".popup-content");
        const $arrow = $(this);

        $content.slideToggle(200, function () {
          $arrow.text($content.is(":visible") ? "▼" : "▲");
        });
      });

    $popup
      .find(".copy")
      .off("click")
      .on("click", function () {
        const $copyButton = $(this);
        const contentToCopy = $popup.find(".popup-content").text().trim();

        navigator.clipboard
          .writeText(contentToCopy)
          .then(function () {
            alert("Content copied to clipboard.");
          })
          .catch(function (err) {
            console.error("Failed to copy text:", err);
            alert("Failed to copy text. Please try again.");
          });
      });

    $popup.find(".popup-title").on("mousedown", function () {
      $($popup).css("z-index", ++popupZIndex);
    });
  }

  // New function to initialize sortable functionality
  function initializeSortable($container) {
    $container
      .sortable({
        items: ".popup",
        handle: ".popup-title",
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
        update: function () {
          console.log("Popup order updated.");
          // You might want to save the new order here
        },
      })
      .sortable("refresh");
  }

  function makeDroppable($container) {
    $container.droppable({
      accept: ".popup",
      hoverClass: "ui-state-highlight",
      drop: function (event, ui) {
        var $this = $(this);
        var $droppedPopup = ui.helper;

        // Check if already pinned
        if (!$droppedPopup.hasClass("pinned")) {
          $this.append($droppedPopup);
          $droppedPopup
            .css({
              position: "relative",
              top: "auto",
              left: "auto",
              width: "100%",
            })
            .addClass("pinned");

          // Disable dragging after being pinned
          $droppedPopup.draggable("option", "disabled", true);
        }

        // Initialize sortable after a manual drop
        initializeSortable($this);
      },
    });
  }

  // Event listeners
  $(document).on("click", "#save-pinned-popups", savePinnedPopups);
  $(document).on("click", "#fetch-pinned-popups", fetchPinnedPopups);

  // Auto-fetch popups when a client is selected
  $(document).on("clientSelected", function () {
    fetchPinnedPopups();
  });

  // Update the droppable area HTML to include save and fetch buttons
  $(document).ready(function () {
    // Modify the droppable area HTML
    $("#drag-area-right .card-body").prepend(`
            <div class="droppable-controls d-flex justify-content-between mb-2">
                <div>
                    <button id="save-pinned-popups" class="btn btn-save btn-sm btn-primary mr-2">
                        <i class="fas fa-save"></i> Save Popups
                    </button>
                    <button id="fetch-pinned-popups" class="btn btn-fetch btn-sm btn-secondary">
                        <i class="fas fa-download"></i> Fetch Popups
                    </button>
                </div>
                <button id="clear-pinned-popups" class="btn btn-clear btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Clear Popups
                </button>
            </div>
        `);

    // Add clear popups functionality
    $(document).on("click", "#clear-pinned-popups", function () {
      $(".nested-droppable .popup").remove();

      // Optional: Clear from server-side as well
      $.ajax({
        url: BASE_URL + "/jurislocator_version_2/user/api/save_pinned_popups.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
          action: "clear",
        }),
        success: function (response) {
          if (response.success) {
            Swal.fire({
              icon: "success",
              title: "Cleared",
              text: "All pinned popups have been removed",
              toast: true,
              position: "top-end",
              showConfirmButton: false,
              timer: 3000,
            });
          }
        },
      });
    });

    // Automatically fetch pinned popups when a client is selected
    $(document).on("clientSelected", function () {
      fetchPinnedPopups();
    });
  });

  // Extend existing droppable functionality
  $(".nested-droppable").droppable({
    accept: ".popup",
    hoverClass: "ui-state-highlight",
    drop: function (event, ui) {
      var $this = $(this);
      if (!ui.helper.hasClass("pinned")) {
        $this.append(ui.helper);
        ui.helper
          .css({
            position: "relative",
            top: "auto",
            left: "auto",
            width: "100%",
          })
          .addClass("pinned");
        ui.helper.draggable("option", "disabled", true);
      }
      $this
        .sortable({
          items: ".popup",
          handle: ".popup-title",
          placeholder: "ui-state-highlight",
          forcePlaceholderSize: true,
          update: function () {
            ui.item.css({
              width: "100%",
              position: "relative",
              top: "auto",
              left: "auto",
            });
            console.log("Popup order updated.");
          },
        })
        .sortable("refresh");
    },
  });

  function showSuccessMessage(message) {
    const $successMessage = $("#success-message");
    if (!$successMessage.length) {
      $("<div>", {
        id: "success-message",
        text: message,
        style:
          "display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); background-color:#4CAF50; color:white; padding:10px 20px; border-radius:4px; z-index:9999;",
      })
        .appendTo("body")
        .fadeIn(200)
        .delay(1500)
        .fadeOut(200);
    } else {
      $successMessage.text(message).fadeIn(200).delay(1500).fadeOut(200);
    }
  }

  function handleError(error) {
    console.error("Operation failed:", error);
    alert("Operation failed. Please try again.");
  }

  // Main click handler for references
  $(document).on("click", ".ref:not(.popup-content .ref)", function (e) {
    e.preventDefault();
    const sectionId = $(this).data("section-id");
    // Get the category ID and act name from data attributes
    const categoryId = $(this).data("category-id") || 1;
    const actName = $(this).data("act-name");
    const mouseX = e.pageX;
    const mouseY = e.pageY;

    if (!sectionId) {
      console.error("No section ID found");
      return;
    }

    fetchAndDisplayPopup(sectionId, categoryId, mouseX, mouseY, actName);
  });

  // Separate handler for nested references
  $(document).on("click", ".popup-content .ref", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const sectionId = $(this).data("section-id");
    // Get the category ID from data attribute, with a default of 1 only for non-cross-act refs
    const isXRef = $(this).hasClass("cross-act-ref");
    const categoryId = $(this).data("category-id") || (isXRef ? null : 1);
    const mouseX = e.pageX;
    const mouseY = e.pageY;

    if (!sectionId) {
      console.error("No section ID found for nested reference");
      return;
    }

    fetchAndDisplayPopup(sectionId, categoryId, mouseX, mouseY);
  });

  // Ensure makeLinksClickable function is defined similarly to other references
  function makeLinksClickable(text, categoryId) {
    // Create a map to track processed positions in the text
    let processedPositions = new Set();

    // PRIORITY 1: Handle cross-act references first
    text = text.replace(
      /\b(subsection|section|paragraph)\s+(\d+(?:\.\d+)?(?:\([^)]+\))*)\s*\(([^)]*?(?:Immigration Division Rules|Division Rules|Immigration and Refugee Protection Act|Rules|Act|Regulations)[^)]*)\)/gi,
      function (match, type, sectionId, actName, offset) {
        // Mark this range as processed
        for (let i = offset; i < offset + match.length; i++) {
          processedPositions.add(i);
        }

        const fullRef = sectionId.trim() + "(" + actName.trim() + ")";
        // Make only the section reference part clickable
        return (
          '<span class="ref cross-act-ref" ' +
          'data-section-id="' + sectionId.trim() + '" ' +
          'data-act-name="' + actName.trim() + '">' +
          type + ' ' + sectionId.trim() +
          '</span> (' + actName.trim() + ')'
        );
      }
    );

    // Handle regular section references, but only for unprocessed matches
    let lastIndex = 0;
    let result = '';
    const regularRefRegex = /\b(\d+(?:\.\d+)?(?:\([^)]+\))*)\b/g;
    let match;

    while ((match = regularRefRegex.exec(text)) !== null) {
      const start = match.index;
      const end = start + match[0].length;

      // Check if this match overlaps with any processed positions
      let isProcessed = false;
      for (let i = start; i < end; i++) {
        if (processedPositions.has(i)) {
          isProcessed = true;
          break;
        }
      }

      if (!isProcessed) {
        result += text.slice(lastIndex, start);
        result += '<span class="ref" data-section-id="' + match[0] +
          '" data-category-id="' + categoryId + '">' + match[0] + '</span>';
      } else {
        result += text.slice(lastIndex, end);
      }
      lastIndex = end;
    }

    result += text.slice(lastIndex);
    return result;
  }

  function setupNestedPopups(popup) {
    popup.find(".ref").on("click", function (e) {
      e.preventDefault();
      console.log("Nested reference clicked");

      // Check if this is an unmatched reference
      const isUnmatchedRef = $(this).hasClass("unmatched-ref");
      const sectionId = $(this).data("section-id");
      const categoryId = $(this).data("category-id");
      const mouseX = e.pageX;
      const mouseY = e.pageY;

      if (isUnmatchedRef) {
        // For unmatched references, fetch using ref_id
        $.ajax({
          url: BASE_URL + "/api/fetch_unmatched_reference.php",
          method: "POST",
          data: { ref_id: sectionId },
          dataType: "json",
          success: function (data) {
            if (data.error) {
              console.error("Server Error:", data.error, data.details);
              alert("Unable to fetch reference: " + data.error);
            } else {
              displayUnmatchedPopup(data, mouseX, mouseY);
            }
          },
          error: function (xhr, status, error) {
            console.error("Ajax request failed:", status, error);
            try {
              var errorData = JSON.parse(xhr.responseText);
              alert("Error: " + (errorData.error || "Unknown error"));
            } catch (e) {
              alert("An unexpected error occurred");
            }
          },
        });
      } else {
        // For regular references
        fetchAndDisplayPopup(sectionId, categoryId, mouseX, mouseY);
      }
    });
  }

  function displayUnmatchedPopup(refInfo, mouseX, mouseY) {
    if (!refInfo || !refInfo.content) {
      console.error("Invalid unmatched reference data:", refInfo);
      alert("Unable to display reference content.");
      return;
    }

    var popup = $("#popup-template").clone().removeAttr("id");

    // Improve title generation
    var titleText = refInfo.full_path || "Unmatched Reference";
    popup.find(".title-text").text(titleText);

    // Use makeLinksClickable with fallback category
    var processedContent = makeLinksClickable(
      refInfo.content || "No content available",
      refInfo.category_id || 1
    );

    popup.find(".popup-content").html(processedContent);

    popup.css({
      top: mouseY + 10 + "px",
      left: mouseX + 10 + "px",
      zIndex: ++popupZIndex,
      display: "block",
    });

    $("body").append(popup);
    makeDraggable(popup);

    // Updated nested reference handling
    setupNestedPopups(popup);

    // Existing popup setup handlers
    setupPopupHandlers(popup);

    console.log("Unmatched popup display complete");
  }

  // Add a general click handler for any unmatched references
  $(document).on("click", ".ref.unmatched-ref", function (e) {
    e.preventDefault();
    const sectionId = $(this).data("section-id");
    const mouseX = e.pageX;
    const mouseY = e.pageY;

    if (!sectionId) {
      console.error("No section ID found for unmatched reference");
      return;
    }

    fetchUnmatchedReference(sectionId, mouseX, mouseY);
  });

  // Existing click handler for unmatched references
  $(document).on("click", "custom-ref.unmatched-ref", function (e) {
    e.preventDefault();
    var refId = $(this).data("ref-id");
    var mouseX = e.pageX;
    var mouseY = e.pageY;

    $.ajax({
      url: BASE_URL + "/api/fetch_unmatched_reference.php",
      method: "POST",
      data: { ref_id: refId },
      dataType: "json",
      success: function (data) {
        if (data.error) {
          console.error("Server Error:", data.error, data.details);
          alert("Unable to fetch reference: " + data.error);
        } else {
          displayUnmatchedPopup(data, mouseX, mouseY);
        }
      },
      error: function (xhr, status, error) {
        console.error("Ajax request failed:", status, error);
        console.error("Response Text:", xhr.responseText);

        try {
          var errorData = JSON.parse(xhr.responseText);
          alert("Error: " + (errorData.error || "Unknown error"));
        } catch (e) {
          alert("An unexpected error occurred");
        }
      },
    });
  });

  function fetchAndDisplayPopup(sectionId, categoryId, mouseX, mouseY) {
    if (!sectionId) {
      console.error("Error: Missing section ID.");
      return;
    }

    // Create a unique popup identifier based on sectionId and categoryId
    var popupKey = sectionId + "-" + (categoryId || "cross");

    // Check if this popup is already open
    if (openedPopups.has(popupKey)) {
      return; // Skip if already open
    }

    // Add to tracking set
    openedPopups.add(popupKey);

    $.ajax({
      url: BASE_URL + '/api/section-content/' + categoryId + '/' + encodeURIComponent(sectionId),
      method: "GET",
      dataType: "json",
      success: function (response) {
        try {
          if (response.error) {
            console.error("Server Error:", response.error);
            // Fallback to unmatched reference if regular fetch fails
            fetchUnmatchedReference(sectionId, mouseX, mouseY);
            return;
          }
          
          // Process the data if it exists
          if (response.data && Array.isArray(response.data) && response.data.length > 0) {
            const section = response.data[0];
            const title = section.title || "Section " + sectionId;
            const content = section.text_content || section.content || "No content available";
            
            createSectionPopup(title, content, section, mouseX, mouseY, sectionId, categoryId);
          } else {
            // If no data or empty array, fallback to unmatched reference
            fetchUnmatchedReference(sectionId, mouseX, mouseY);
          }
        } catch (e) {
          console.error("Error processing response:", e);
          fetchUnmatchedReference(sectionId, mouseX, mouseY);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        
        // Check authentication errors
        if (xhr.status === 401) {
          createErrorPopup("Authentication Required", "You need to be logged in to view this content.", mouseX, mouseY);
          return;
        }
        
        // For all other errors, fallback to unmatched reference
        fetchUnmatchedReference(sectionId, mouseX, mouseY);
      },
    });
  }

  function displayPopup(sectionData, mouseX, mouseY) {
    const mainSection = sectionData[0];
    const popupKey = mainSection.section_id + "-" + mainSection.category_id;

    // Check if we can open this popup
    if (!canOpenPopup(popupKey)) return;

    // Create and show the popup
    const $popup = $("#popup-template").clone().removeAttr("id");

    const titleText = getFullSectionIdentifier(mainSection);

    $popup.find(".title-text").text(titleText);
    $popup
      .find(".popup-content")
      .html(generateHierarchicalContent(sectionData, mainSection.category_id));

    // Missing attributes: Ensure section_id and category_id are added
    $popup.attr("data-section-id", mainSection.section_id);
    $popup.attr("data-category-id", mainSection.category_id);

    $popup.css({
      top: mouseY + 10 + "px",
      left: mouseX + 10 + "px",
      zIndex: ++popupZIndex,
      display: "block",
    });

    $("body").append($popup);
    makeDraggable($popup);

    setupPopupHandlers($popup, popupKey);

    // Increment counter AFTER confirming popup is shown
    activePopups[popupKey] = (activePopups[popupKey] || 0) + 1;

    console.log(`Popup ${popupKey} opened ${activePopups[popupKey]} time(s)`);
  }

  function getFullSectionIdentifier(section) {
    let identifier = "";
    if (section.part) identifier += "Part " + section.part + " ";
    if (section.division) identifier += "Division " + section.division + " ";
    if (section.section_id) identifier += "Section " + section.section_id;
    return identifier.trim();
  }

  function generateHierarchicalContent(sectionData, categoryId) {
    var content = "";
    sectionData.forEach(function (section) {
      var sectionClass = getSectionClass(section);
      content += "<div class='" + sectionClass + "'>";

      // Include source table information in the content if from a different act
      if (section.from_other_category === 1) {
        content +=
          "<div class='source-info'>" + section.source_table + "</div>";
      }

      if (section.section_id) {
        content += "<h4>" + section.title + "</h4>";
        content +=
          "<p>" +
          makeLinksClickable(section.text_content, section.category_id) +
          "</p>";
      } else {
        content += "<h4>" + section.title + "</h4>";
        content +=
          "<p>" +
          makeLinksClickable(section.text_content, section.category_id) +
          "</p>";
      }

      // Fix: Add footnote display
      if (section.footnote) {
        content += "<p class='footnote'>" + section.footnote + "</p>";
      }

      content += "</div>";
    });
    return content;
  }

  // Track opened popups to prevent duplicates
  let openedPopups = new Set();

  function makeLinksClickable(text, categoryId) {
    // Create a map to track processed positions in the text
    let processedPositions = new Set();

    // PRIORITY 1: Handle cross-act references first
    text = text.replace(
      /\b(subsection|section|paragraph)\s+(\d+(?:\.\d+)?(?:\([^)]+\))*)\s*\(([^)]*?(?:Immigration Division Rules|Division Rules|Immigration and Refugee Protection Act|Rules|Act|Regulations)[^)]*)\)/gi,
      function (match, type, sectionId, actName, offset) {
        // Mark this range as processed
        for (let i = offset; i < offset + match.length; i++) {
          processedPositions.add(i);
        }

        const fullRef = sectionId.trim() + "(" + actName.trim() + ")";
        // Make only the section reference part clickable
        return (
          '<span class="ref cross-act-ref" ' +
          'data-section-id="' + sectionId.trim() + '" ' +
          'data-act-name="' + actName.trim() + '">' +
          type + ' ' + sectionId.trim() +
          '</span> (' + actName.trim() + ')'
        );
      }
    );

    // Handle regular section references, but only for unprocessed matches
    let lastIndex = 0;
    let result = '';
    const regularRefRegex = /\b(\d+(?:\.\d+)?(?:\([^)]+\))*)\b/g;
    let match;

    while ((match = regularRefRegex.exec(text)) !== null) {
      const start = match.index;
      const end = start + match[0].length;

      // Check if this match overlaps with any processed positions
      let isProcessed = false;
      for (let i = start; i < end; i++) {
        if (processedPositions.has(i)) {
          isProcessed = true;
          break;
        }
      }

      if (!isProcessed) {
        result += text.slice(lastIndex, start);
        result += '<span class="ref" data-section-id="' + match[0] +
          '" data-category-id="' + categoryId + '">' + match[0] + '</span>';
      } else {
        result += text.slice(lastIndex, end);
      }
      lastIndex = end;
    }

    result += text.slice(lastIndex);
    return result;
  }

  function getSectionClass(section) {
    if (section.sub_paragraph) return "subparagraph";
    if (section.paragraph) return "paragraph";
    if (section.sub_section) return "subsection";
    return "section";
  }

  //advanced searching script
  $(document).ready(function () {
    function initializeSelect2() {
      // Initialize Select2 for multi-select fields
      $(".select2Multiselect").select2({
        allowClear: true,
        width: "100%",
      });
    }

    // Function to get URL parameter
    function getUrlParameter(name) {
      name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
      var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
      var results = regex.exec(location.search);
      return results === null ? "" : decodeURIComponent(results[1]);
    }

    // Function to update current category ID based on URL parameter
    function updateCurrentCategory() {
      var currentCategoryId = getUrlParameter("category_id");

      if (currentCategoryId) {
        // Ensure it's a valid number
        if (/^\d+$/.test(currentCategoryId)) {
          $("#current-table").val(currentCategoryId);
        } else {
          // Handle cases with multiple category IDs (comma-separated)
          const firstCategoryId = currentCategoryId.split(",")[0];
          $("#current-table").val(firstCategoryId);
          console.warn(
            "Multiple categories detected, using first:",
            firstCategoryId
          );
        }
      }
    }

    // Fetch and populate act names
    function populateActNames() {
      $.ajax({
        url: BASE_URL + "/jurislocator_version_2/user/api/search.php?get_act_names=1",
        method: "GET",
        dataType: "json",
        success: function (actNames) {
          const actNameSelect = $("#act-names-select");
          actNameSelect.empty();

          // Filter out null or empty values
          const validActNames = actNames.filter(function (actName) {
            return actName !== null && actName.trim() !== "";
          });

          // Create an option for each valid act name
          validActNames.forEach(function (actName) {
            actNameSelect.append(
              `<option value="${actName}">${actName}</option>`
            );
          });

          // Initialize Select2 after populating the select element
          initializeSelect2();
        },
        error: function () {
          console.error("Failed to fetch act names");
        },
      });
    }

    // Call this function when the page loads
    updateCurrentCategory();
    populateActNames();

    // Search form submission handler
    const loadingContainer = $(".loading-container");
    const advancedSearchBtn = document.getElementById("advanced-search-btn");
    const advancedSearchContainer = document.querySelector(
      ".advanced-search-container"
    );
    let searchTimeout;

    // Toggle between basic and advanced search
    advancedSearchBtn.addEventListener("click", () => {
      const basicSearchContainer = document.getElementById(
        "basic-search-container"
      );
      basicSearchContainer.classList.add("d-none");
      advancedSearchContainer.classList.remove("d-none");

      // Ensure first checkbox is checked by default
      $("#act-names-container input:first").prop("checked", true);
    });

    // Back to basic search
    $(document).on("click", "#basic-search-btn", function () {
      const basicSearchContainer = $("#basic-search-container");
      const advancedSearchContainer = $(".advanced-search-container");

      basicSearchContainer.removeClass("d-none");
      advancedSearchContainer.addClass("d-none");
    });

    // Basic Search form submission handler
    $("#search-form").on("submit", function (e) {
      e.preventDefault();

      // Get basic search criteria
      var keyword = $("#keyword").val();

      if (!keyword.trim()) {
        alert("Please enter a keyword to search.");
        $("#keyword").focus();
        return;
      }

      var currentOnly = $("#current-only").is(":checked") ? 1 : 0;
      var currentCategoryId = $("#current-table").val();
      var table = currentOnly ? currentCategoryId : "all";

      // Show loading animation
      loadingContainer.addClass("active");

      // Clear previous timeout if exists
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }

      // Add minimum loading time for better UX
      searchTimeout = setTimeout(function () {
        $.ajax({
          url: BASE_URL + "/jurislocator_version_2/user/api/search.php",
          method: "POST",
          data: {
            keyword: keyword,
            table: table,
            current_only: currentOnly,
          },
          success: function (response) {
            // Hide loading animation
            loadingContainer.removeClass("active");

            // Update content with animation
            $("#content").html(response);

            // Add sequential animation to act containers
            $(".act-container").each(function (index) {
              $(this).css("animation-delay", `${0.1 * (index + 1)}s`);
            });

            // Initialize any additional functionality
            if (typeof initializePopups === "function") {
              initializePopups();
            }
          },
          error: function (xhr, status, error) {
            // Hide loading animation
            loadingContainer.removeClass("active");

            console.error("Search Error:", status, error);
            alert("An error occurred while searching. Please try again.");
          },
        });
      }, 800);
    });

    // Advanced search form submission handler
    $("#advanced-search-form").on("submit", function (e) {
      e.preventDefault();

      // Get advanced search criteria
      var keyword = $("#advanced-keyword").val();
      var exactPhrase = $("#exact-phrase").val();
      var withTheseWords = $("#with-these-words").val();
      var exceptTheseWords = $("#except-these-words").val();
      var title = $("#title-search").val();
      var section = $("#section-search").val();
      var subSection = $("#sub-section-search").val();

      // Get selected act names/tables
      var selectedTables = $("#act-names-select").val(); // Retrieves an array of selected values

      var isAnyFieldFilled =
        keyword.trim() ||
        exactPhrase.trim() ||
        withTheseWords.trim() ||
        exceptTheseWords.trim() ||
        title.trim() ||
        section.trim() ||
        subSection.trim() ||
        (selectedTables && selectedTables.length > 0);

      if (!isAnyFieldFilled) {
        alert("Please fill at least one search field.");
        $("#advanced-keyword").focus();
        return;
      }

      // Show loading animation
      loadingContainer.addClass("active");

      // Clear previous timeout if exists
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }

      // Add minimum loading time for better UX
      searchTimeout = setTimeout(function () {
        $.ajax({
          url: BASE_URL + "/jurislocator_version_2/user/api/search.php",
          method: "POST",
          data: {
            keyword: keyword,
            exact_phrase: exactPhrase,
            with_these_words: withTheseWords,
            except_these_words: exceptTheseWords,
            title: title,
            section: section,
            sub_section: subSection,
            selected_tables: selectedTables, // Pass the array of selected tables
          },
          success: function (response) {
            // Hide loading animation
            loadingContainer.removeClass("active");

            // Update content with animation
            $("#content").html(response);

            // Add sequential animation to act containers
            $(".act-container").each(function (index) {
              $(this).css("animation-delay", `${0.1 * (index + 1)}s`);
            });

            // Initialize any additional functionality
            if (typeof initializePopups === "function") {
              initializePopups();
            }
          },
          error: function (xhr, status, error) {
            loadingContainer.removeClass("active");
            console.error("Search Error:", xhr.responseText || status, error);
            alert("An error occurred while searching. Please try again.");
          },
        });
      }, 800);
    });

    // Optional: Add keyboard shortcut for search
    $(document).keydown(function (e) {
      if (e.ctrlKey && e.key === "f") {
        e.preventDefault();
        $("#keyword").focus();
      }
    });

    // Optional: Handle changes in URL without page reload
    $(window).on("popstate", function () {
      updateCurrentCategory();
    });
  });

  function setupPopupHandlers(popup, popupKey) {
    popup
      .find(".close")
      .off("click")
      .on("click", function () {
        // Get the popup identifier before removing
        const sectionId = popup.attr("data-section-id");
        const categoryId = popup.attr("data-category-id") || "cross";
        const popupKey = sectionId + "-" + categoryId;

        // Remove from tracking set
        openedPopups.delete(popupKey);

        // Remove the popup
        popup.remove();
      });

    popup
      .find(".arrow")
      .off("click")
      .on("click", function () {
        const $content = popup.find(".popup-content");
        const $arrow = $(this);

        $content.slideToggle(200, function () {
          $arrow.text($content.is(":visible") ? "▼" : "▲");
        });
      });

    // Copy handler - Fixed with proper content formatting and feedback
    popup
      .find(".copy")
      .off("click")
      .on("click", function () {
        const $copyButton = $(this);
        const contentToCopy = getFormattedContent(popup);

        navigator.clipboard
          .writeText(contentToCopy)
          .then(function () {
            // Visual feedback
            $copyButton.addClass("copied");

            // Create and show feedback message
            const $feedback = $("<div>")
              .addClass("copy-feedback")
              .text("Copied!")
              .css({
                position: "absolute",
                right: "40px",
                top: "5px",
                background: "#4CAF50",
                color: "white",
                padding: "4px 8px",
                borderRadius: "4px",
                fontSize: "12px",
                opacity: "0",
                transition: "opacity 0.2s",
              })
              .appendTo($copyButton.parent());

            // Animate feedback
            setTimeout(() => $feedback.css("opacity", "1"), 50);
            setTimeout(() => {
              $feedback.css("opacity", "0");
              setTimeout(() => {
                $feedback.remove();
                $copyButton.removeClass("copied");
              }, 200);
            }, 1500);
          })
          .catch(function (err) {
            console.error("Failed to copy text:", err);
            alert("Failed to copy text. Please try again.");
          });
      });
  }

  function initializePopups() {
    $(".search-result").each(function () {
      var sectionId = $(this).data("section-id");
      var categoryId = $(this).data("category-id");
      $(this).on("click", function (e) {
        e.preventDefault();
        var popupKey = sectionId + "-" + categoryId;
        if (!activePopups[popupKey]) {
          var mouseX = e.pageX;
          var mouseY = e.pageY;
          fetchAndDisplayPopup(sectionId, categoryId, mouseX, mouseY);
        }
      });
    });
  }

  // Function to get the formatted content of a popup
  function getFormattedContent(popup) {
    const titleText = popup.find(".title-text").text().trim();
    const contentText = popup
      .find(".popup-content")
      .clone() // Clone the element
      .find("script")
      .remove()
      .end() // Remove any script tags
      .text() // Get the text content
      .trim() // Remove leading/trailing whitespace
      .replace(/\s+/g, " "); // Normalize whitespace

    /**
     * Create an error popup
     * @param {string} title - Popup title
     * @param {string} message - Error message to display
     * @param {number} x - X position for the popup
     * @param {number} y - Y position for the popup
     */
    function createErrorPopup(title, message, x, y) {
      const $popup = $("<div>")
        .addClass("popup section-popup")
        .css({
          left: x,
          top: y,
          zIndex: 1000,
          minWidth: "300px",
          maxWidth: "500px",
        })
        .append(
          $("<div>")
            .addClass("popup-header")
            .append(
              $("<h2>").text(title),
              $("<button>")
                .addClass("close-btn")
                .html("&times;")
                .on("click", function () {
                  $popup.remove();
                })
            )
        )
        .append(
          $("<div>")
            .addClass("popup-content")
            .append($("<div>").addClass("alert alert-danger").html(message))
        );

      // Make popup draggable
      $popup.draggable({
        handle: ".popup-header",
        containment: "window",
      });

      // Add to DOM
      $("body").append($popup);
      
      return $popup;
    }

    return `${titleText}\n\n${contentText}`;
  }
  const $sidebarToggle = $(".sidebar-toggle");
  const $sidebar = $(".sidebar");
  const $mainContent = $(".main-content");

  // [REMOVED LEGACY POPUP AJAX LOGIC]
  // The popup logic for fetching legal references is now handled by legal-reference-popups.js and reference-by-id.js.
  // If you need to add custom popup logic, use the new Laravel endpoints: /section-content/{tableId}/{sectionRef} and /reference/{referenceId}.
});
