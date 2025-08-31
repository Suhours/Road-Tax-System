load_payment_report();

function load_payment_report() {
    let keyword = $("#search_payment_info").val().trim();
  
    if (keyword === "") {
      $("#statement_output").hide(); // hide when empty
      return;
    }
  
    $.ajax({
      method: "POST",
      url: "../api/miniReport.php",
      dataType: "json",
      data: {
        action: "load_payments_report",
        keyword: keyword
      },
      success: function (data) {
        if (data.status && data.data.length > 0) {
          const info = data.data[0]; // Show first match only for now
  
          $("#plate_number").text(info.NumberPlate);
          $("#vehicle_type").text(info.VehicleType);
          $("#tax_per_quarter").text(info.TaxPerQuarter);
          $("#owner_name").text(info.OwnerName);
          $("#owner_phone").text(info.OwnerPhone);
          $("#last_payment_date").text(info.LastPaymentDate);
  
          $("#payment_summary").html(`
            <tr>
              <td>${info.QuartersDue}</td>
              <td>$${info.TotalTaxDue}</td>
              <td>$${info.TotalPaid}</td>
              <td class="fw-bold text-danger">$${info.UnpaidBalance}</td>
            </tr>
          `);
  
          $("#statement_output").fadeIn();
        } else {
          $("#statement_output").hide();
          Swal.fire("Not Found", "No matching record found.", "warning");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        Swal.fire("Error", "An error occurred while loading data.", "error");
      }
    });
  }
  
  // Also trigger on input
  $("#search_payment_info").on("input", load_payment_report);
  