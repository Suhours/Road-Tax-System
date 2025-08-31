
$(document).ready(function () {

load_payment_report();




// $("#from").attr("disabled" ,true);
// $("#to").attr("disabled", true);


// $("#type").on("change", function(){

//     if($("#type").val() == 0){
//         $("#from").attr("disabled", true);
//         $("#to").attr("disabled", true);
//     }else{
//         $("#from").attr("disabled", false);
//         $("#to").attr("disabled", false);
//     }

// })


$("#print_statement").on("click", function(){

    print_statement();

})

function print_statement(){

    let printArea = document.querySelector("#print_area");
    let newWindow = window.open("");
    newWindow.document.write(`<html><head><title></title>`);
    newWindow.document.write(`<style  media="print">


         @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        body {
        
         font-family: "Poppins", serif;
        }
         .poppins-bold {
           font-family: "Poppins", serif;
           font-weight: 700;
           font-style: normal;
        }

        table {
        width: 100%;
        }
        th {
        background-color: #00b7f2 !important;
        color: white    !important;             
        }
        th, td{
        padding: 5px !important;
        text-align: left !important;
        }
        th, td{
        border-left: 1px solid #ddd  !important;
        
        border-bottom: 1px solid #ddd  !important;
        }
        
        
        
        
        </style>`);


    newWindow.document.write(`</head><body>`);
    newWindow.document.write(printArea.innerHTML);
    newWindow.document.write(`</body></html>`);
    newWindow.print();
    newWindow.close();    

}


$("#export_statement").on('click', function(){
    let file = new Blob([$('#print_area').html()], {type:"application/vnd.ms-excel"});
    let url = URL.createObjectURL(file);
    let a = $("<a />", {

        href: url,
        download: "print_statement.xls"}).appendTo("body").get(0).click();
        e.preventDefault();
})

$("#pdf_statement").on('click', function () {
    const printArea = document.getElementById('print_area');

    html2canvas(printArea).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jspdf.jsPDF('p', 'mm', 'a4');
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save('vehicle_report.pdf');
    });
});



$("#payment_Report").on("submit", function(event){
    event.preventDefault();

   $("#vehicle_report thead").empty();
   $("#vehicel_report tbody").empty();

   let from = $("#from").val();
   let to   = $("#to").val();

   

   let     sendingData = {
           
             "from": from,
             "to": to,
            "action": "get_vehicle_by_date"
       }



    $.ajax( {
        method : "POST",
        dataType: "JSON",
        url : "../api/paymentReport.php",
        data : sendingData,
        success: function(data){
            let status = data.status;
            let response= data.data;

            let tr = '';
            let th = '';
            if(status){

                th = "<tr>";
                for(let r in response[0]){
                    th += `<th>${r}</th>`;
                }
                th += "</tr>";
                $("#vehicle_report thead").append(th);

                response.forEach(res => {
                    tr = "<tr>";
                    for(let r in res){
                        tr += `<td>${res[r]}</td>`;
                    }
                    tr += "</tr>";
                    $("#vehicle_report tbody").append(tr);
                })
                
            }else{
               Alerts("error",response);
            }

        },
        error: function(data){
           console.error(data);

        }
    })
})








$("#search_payment_info").on("input", function () {
    load_payment_report();
});

function load_payment_report() {
    let keyword = $("#search_payment_info").val().trim();

    $.ajax({
        method: "POST",
        url: "../api/paymentReport.php",
        dataType: "json",
        data: {
            action: "load_payments_report",
            keyword: keyword
        },
        success: function (data) {
            // Ka saar rows hore
            $("#payment_report tbody").empty();

            if (data.status) {
                let rows = "";

                data.data.forEach(function (row) {
                    rows += "<tr>";
                    for (let key in row) {
                        if (key === "AmountPerThreeMonth") {
                            rows += `<td>$${row[key]}</td>`;
                        }else if(key === "TotalTaxDue"){
                            rows += `<td>$${row[key]}</td>`;
                        }else if(key === "TotalPaid"){
                            rows += `<td>$${row[key]}</td>`;
                        }else if(key === "UnpaidBalance"){
                            rows += `<td>$${row[key]}</td>`;
                        } else {
                            rows += `<td>${row[key]}</td>`;
                        }
                    }
                    rows += "</tr>";
                });

                $("#payment_report tbody").append(rows);
            } else {
                alert("Xog lama helin: " + data.data);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            alert("Khalad dhacay: " + error);
        }
    });
}

});