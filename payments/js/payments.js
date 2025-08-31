$(document).ready(function() {
    // load_payment_info();

    load_payment_info();
    fetchVehicleInfo();


    btnAction = "Insert";

    // Show modal for adding new payment
    $("#add_payment").on("click", function() {
        btnAction = "Insert";
        $("#payment_form")[0].reset();
        $("#id").val("");
        $("#payment_modal").modal("show");
    });

 
    // $("#payment_form").on("submit", function(event) {
    //     event.preventDefault();
    //     const plateNumber = $("#number_plate").val().trim();
    //     if(plateNumber.length < 3) {
    //         Swal.fire("Error", "Please enter a valid plate number", "error");
    //         return;
    //     }

    //     // Step 1: Get vehicle info
    //     $.ajax({
    //         method: "POST",
    //         url: "../api/payments.php",
    //         data: {
    //             action: "payment_registration",
    //             number_plate: plateNumber
    //         },
    //         success: function(vehicleData) {
    //             if(vehicleData.status) {
    //                 // Step 2: Now record the payment
    //                 const formData = new FormData();
    //                 formData.append('action', 'record_payment');
    //                 formData.append('vehicle_id', vehicleData.data.id);
    //                 formData.append('number_plate', plateNumber);
    //                 formData.append('amount', $("#amount").val());
    //                 formData.append('reciept_number', $("#reciept_number").val().split('\\').pop());
                    
    //                 if($("#reciept_number")[0].files[0]) {
    //                     formData.append('receipt_file', $("#reciept_number")[0].files[0]);
    //                 }

    //                 $.ajax({
    //                     method: "POST",
    //                     url: "../api/payments.php",
    //                     data: formData,
    //                     processData: false,
    //                     contentType: false,
    //                     success: function(paymentResponse) {
    //                         if(paymentResponse.status) {
    //                             Swal.fire("Success", paymentResponse.data, "success")
    //                                 .then(() => {
    //                                     $("#payment_modal").modal("hide");
    //                                     load_payment_info();
    //                                 });
    //                         } else {
    //                             Swal.fire("Error", paymentResponse.data, "error");
    //                         }
    //                     },
    //                     error: function(xhr) {
    //                         Swal.fire("Error", xhr.responseText || "Payment failed", "error");
    //                     }
    //                 });
    //             } else {
    //                 Swal.fire("Error", vehicleData.data, "error");
    //             }
    //         },
    //         error: function(xhr) {
    //             Swal.fire("Error", xhr.responseText || "Failed to get vehicle info", "error");
    //         }
    //     });
    // });

    // // Auto-fill vehicle info when plate number is entered
    // $("#number_plate").on("input", function() {
    //     const keyword = $(this).val().trim();
        
    //     if(keyword.length >= 3) {
    //         $.ajax({
    //             method: "POST",
    //             url: "../api/payments.php",
    //             data: {
    //                 action: "payment_registration",
    //                 number_plate: keyword
    //             },
    //             success: function(data) {
    //                 if(data.status) {
    //                     // Make sure to set the vehicle ID
    //                     $("#vehicle_id").val(data.data.id); // Add this hidden field
    //                     $("#owner_name").val(data.data.owner_name);
    //                     $("#amount").val(data.data.amount_per_three_month);
    //                     $("#vehicle_type").val(data.data.type);
    //                     enableFields(true);
    //                 } else {
    //                     clearVehicleFields();
    //                 }
    //             }
    //         });
    //     }
    // });

    // Rest of your event handlers...


    $("#payment_form").on("submit", function(event) {
        event.preventDefault();
    
        let number_plate = $("#number_plate_input").val();
        let amount = $("#amount_paid").val();
        let description = $("#description").val();
        let id = $("#id").val();
    
        let sendingData = {}
        
    
        if(btnAction == "Insert"){
    
    
            sendingData = {
                "number_plate" : number_plate,
                "amount" : amount,
                "description" : description,
                "action" : "payment_registration"
            }
        }else{
    
            sendingData = {
        
                "id"  : id,
                "name" : name,
                "link" : link,
                "category_id" : category_id,
                "action" : "update_link"
            }
    
        }
    
        $.ajax ( { 
            method : "POST",
            dataType : "JSON",
            url : "../api/payments.php",
            data: sendingData,
            success: function(data){
                let status = data.status;
                let response = data.data;
                let html = '';
                let tr = '';
    
                if(status){
                    Allerts("success", response);
                    btnAction = "Insert";
                    // $("#linkTable tbody").empty();
                    // load_links();
                }else{
                    Allerts("error", response);
                }
            },
            error: function(data){
                Allerts("error", data.responseText);
            }
        });
    
    });





});







// Marka user-ku wax ku qorayo number plate-ka
$("#number_plate_input").on("input", function () {
    let plate = $(this).val().trim();

    if (plate.length >= 2) {
        fetchVehicleInfo(plate); // raadso xogta
    } else {
        clearVehicleInputs(); // haddii la tirtiray
    }
});

// Function xogta backend-ka kasoo qaadayaa
function fetchVehicleInfo(number_plate) {
    $.ajax({
        method: "POST",
        url: "../api/payments.php",
        data: {
            action: "get_vehicle_info",
            number_plate: number_plate
        },
        dataType: "JSON",
        success: function(response) {
            if (response.status) {
                let info = response.data;

                
                $("#unpaid_balance").val(info.unpaid_balance);

                let unpaid = parseFloat(info.unpaid_balance);
                if (unpaid > 0) {
                    $("#amount_paid").val(unpaid);
                } else {
                    $("#amount_paid").val("");
                }
            } else {
                clearVehicleInputs();
            }
        },
        error: function(err) {
            console.error("AJAX Error:", err);
            clearVehicleInputs();
        }
    });
}

// Clear inputs haddii number_plate sax ah la waayo
function clearVehicleInputs() {
    $("#unpaid_balance").val("");
    $("#amount_paid").val("");
}


function load_payment_info(){
    let keyword = $("#search_vihicle_info").val().trim();
    $.ajax({
        method: "POST",
        dataType: "JSON",
        url: "../api/payments.php",
        data: {
            action: "load_payment_records",
            keyword: keyword
        },
        success: function(data) {
            $("#payment_table tbody").empty();
            
            if (data.status) {
                data.data.forEach((payment_record, index) => {
                    let row = `
                        <tr>
                        
                            <td>${payment_record.id}</td>
                            <td>${payment_record.number_plate}</td>
                            <td>$${payment_record.amount_paid}</td>
                            <td>${payment_record.description}</td>
                            <td>${payment_record.payment_date}</td>
                            
                        </tr>`;
                    $("#payment_table tbody").append(row);
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: data.data,
                    icon: "error"
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: "Error!",
                text: xhr.responseText || "Failed to load data",
                icon: "error"
            });
        }
    });
}

function fetch_payment_info(id) {
    $.ajax({
        method: "POST",
        dataType: "JSON",
        url: "../api/payments.php",
        data: {
            action: "fetch_payment_info",
            id: id
        },
        success: function(data) {
            if (data.status) {
                btnAction = "Update";
                $("#id").val(data.data.id);
                $("#owner_name").val(data.data.owner_name);
                $("#number_plate").val(data.data.number_plate);
                $("#amount").val(data.data.amount);
                $("#status").val(data.data.status);
                $("#reciept_number").val(data.data.reciept_number);
                $("#payment_modal").modal("show");
            } else {
                Swal.fire({
                    title: "Error!",
                    text: data.data,
                    icon: "error"
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: "Error!",
                text: xhr.responseText || "Failed to fetch data",
                icon: "error"
            });
        }
    });
}

function delete_payment(id) {
    $.ajax({
        method: "POST",
        dataType: "JSON",
        url: "../api/payments.php",
        data: {
            action: "delete_payment",
            id: id
        },
        success: function(data) {
            if (data.status) {
                Swal.fire({
                    title: "Deleted!",
                    text: data.data,
                    icon: "success"
                }).then(() => {
                    load_payment_info();
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: data.data,
                    icon: "error"
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: "Error!",
                text: xhr.responseText || "Failed to delete",
                icon: "error"
            });
        }
    });
}

function Allerts(type,message){
    let success = document.querySelector(".alert-success");
    let error = document.querySelector(".alert-danger");


    if(type == "success"){
        error.classList = "alert alert-danger d-none";
        success.classList="alert alert-success";
        success.innerHTML = message;

        setTimeout(function(){

            
            success.classList= "alert alert-success d-none";
            $("#payment_form")[0].reset();

        },3000);
    }
    else{
        error.classList ="alert alert-danger";
        error.innerHTML = message;
    }
}
