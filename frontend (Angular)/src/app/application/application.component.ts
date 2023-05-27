import { Component, OnInit, ViewChild, ElementRef } from "@angular/core";
import Swal from "sweetalert2";
import { ApiService } from "../api.service";
import {
  NgbDropdownConfig,
  NgbDateStruct,
  NgbCalendar,
  NgbDate,
  NgbDateParserFormatter,
} from "@ng-bootstrap/ng-bootstrap";
import { Router } from "@angular/router";
import { FormGroup } from "@angular/forms";

@Component({
  selector: "app-application",
  templateUrl: "./application.component.html",
  styleUrls: ["./application.component.css"],
  providers: [NgbDropdownConfig],
})
export class ApplicationComponent implements OnInit {
  @ViewChild("dpFromDate", { static: false }) fromDateElement: ElementRef;
  @ViewChild("dpToDate", { static: false }) toDateElement: ElementRef;
  buckets = [
    { name: "Internal Reviewed", code: "internal_reviewed" },
    { name: "Verified OTP", code: "verified_otp" },
    { name: "Processed", code: "processed" },
    { name: "Uncontactable", code: "uncontactable" },
    { name: "Not Interested", code: "not_interested" },
    { name: "Approved", code: "approved" },
    { name: "Rejected", code: "rejected" },
  ];
  changeBuckets = [
    { name: "Processed", code: "processed" },
    { name: "Uncontactable", code: "uncontactable" },
    { name: "Not Interested", code: "not_interested" },
    { name: "Approved", code: "approved" },
    { name: "Rejected", code: "rejected" },
  ];
  applicant_status: any;
  applicant_id = [];
  disbursementFailed = [];
  totalDisbursementFailed = 0;

  checkapp: any;
  applicant: any;
  data: any;
  status: any;
  isLoaded = false;
  month = "Select Month";
  year = "Select Year";
  app_count: number = 0;
  dataLoaded = false;
  filterMessage = "";
  isLoading = true;
  isSuccess: any;
  payload: any;
  paging = true;
  statusName: any;
  statusCode: any;
  selectedAll: any;

  hoveredDate: NgbDate;

  fromDate;
  toDate;
  startDate;
  endDate;
  total;
  page: any;
  sla_aging: number;

  excelBulk: File = null;
  fileToUpload: File = null;
  form: FormGroup;
  token: string = localStorage.getItem("access_token");

  urlExcel: string;

  thisDay = new Date().toJSON().slice(0, 10).replace(/-/g, "-");

  userId = localStorage.getItem("user_id");
  fullName = localStorage.getItem("full_name");
  bankCode = localStorage.getItem("bank_code");
  accessToken = localStorage.getItem("access_token");
  bankName = localStorage.getItem("bank_name");
  datalog = {};
  error;
  message;
  paginationList: any;
  current_page: any;
  lastPage: any;
  isGoHalal: boolean;
  assidq: boolean;
  GsCategory = [
    { id: "app_id", value: "Application ID" },
    { id: "reff_id", value: "Reference ID" },
    { id: "name", value: "Name" },
    { id: "phone_no", value: "Mobile No" },
    { id: "email", value: "Email" },
    { id: "no_ic", value: "NRIC" },
  ];
  GsCategorySrc = "0";
  GsSearchText = "";
  alert_d: boolean;
  sfail_data: any;        // variable to send to html
  success_data: any;        // variable to send to html
  data_s: any;
  data_sf: any;
  alert_message: any;

  constructor(
    private apiService: ApiService,
    private calendar: NgbCalendar,
    public formatter: NgbDateParserFormatter,
    config: NgbDropdownConfig,
    private router: Router
  ) {
    config.placement = "bottom-left";
    config.autoClose = true;
    this.fromDate = "";
    this.toDate = "";
  }

  ngOnInit() {
    this.isGoHalal = false;
    this.assidq = false;
    if (localStorage.getItem("is_gohalal") == "1") {
      this.isGoHalal = true;
      this.buckets = [
        // { name: "Assidq.com reviewed", code: "internal_reviewed" },
        // { name: "Assidq.com rejected", code: "rejected" },
        // { name: "Assidq.com processed", code: "processed" },
        // { name: "Assidq.com uncontactable", code: "uncontactable" },
        // { name: "Assidq.com not Interested", code: "not_interested" },
        { name: "GHF Reviewed", code: "ghf_reviewed" },
        // { name: "GHF LO: Change", code: "ghf_lo_change" },
        // { name: "GHF LO: No", code: "ghf_lo_no" },
        { name: "GHF Emandate Pending", code: "ghf_pending_emandate1" },
        { name: "GHF Emandate Fail", code: "ghf_emandate1_fail" },
        {
          name: "GHF Tawarruq in progress",
          code: "ghf_tawarruq_in_progress",
        },
        { name: "GHF Tawarruq Complete", code: "ghf_tawarruq_complete" },
        { name: "GHF Tawarruq Cancel", code: "ghf_tawarruq_no" },
        { name: "GHF Tawarruq Keep", code: "ghf_tawarruq_keep" },
        {
          name: "GHF Confirm Disbursement",
          code: "ghf_confirm_disbursement",
        },
      ];
      this.changeBuckets = [
        { name: "Rejected", code: "rejected" },
        { name: "Processed", code: "processed" },
        { name: "Uncontactable", code: "uncontactable" },
        { name: "Not Interested", code: "not_interested" },
      ];
      // this.status = "ghf_reviewed";
    }
    if (this.isGoHalal) {
      this.status = "ghf_pending_emandate1";
    } else {
      this.status = "internal_reviewed";
    }
    this.pageLoad(this.status);
  }

  pageLoad(pageStatus) {
    this.page = this.page ? this.page : 1;
    this.status = pageStatus;
    this.applicant_id = [];

    this.statusName = this.buckets.find((x) => x.code == this.status).name;
    this.statusCode = this.buckets.find((x) => x.code == this.status).code;

    localStorage.setItem("curBucket", this.statusCode);

    console.log(this.isGoHalal);

    this.data = {
      status: this.status,
    };
    this.getInitList(this.data);

    if (!(this.isGoHalal == true)) {
      this.urlExcel =
        this.apiService.apiURL +
        "bank-crm/application/export/list-application?status=" +
        this.status +
        "&startDate=&endDate=" +
        this.thisDay +
        "&token=" +
        this.token;
    } else {
      this.urlExcel =
        this.apiService.apiURL +
        "v2/gohalal/excel/export/?status=" +
        this.status +
        "&startDate=&endDate=" +
        this.thisDay +
        "&token=" +
        this.token;
    }
    console.log("urlExcel ", this.urlExcel);
  }

  onExcelPicked(files: FileList) {
    this.fileToUpload = files.item(0);

    let input = new FormData();
    input.append("file", this.fileToUpload);

    console.log(this.excelBulk);
    this.uploadFileToActivity(input);
  }

  onDisbursementPicked(files: FileList) {
    this.fileToUpload = files.item(0);
    let input = new FormData();
    input.append("file", this.fileToUpload);
    this.uploadDisbursementFile(input);
  }

  uploadFileToActivity(input) {
    this.apiService.postExcelBulk(input).subscribe(
      (res: any) => {
        console.log(res);

        this.isSuccess = res.success;
        const messageSuccess = "Upload Excel Success";
        console.log(res.data.failed.applicant);
        console.log(res.data.processed.applicant);
        if (this.isSuccess == true) {
          // Swal.fire({
            // title: "Upload Excel Success",
            // html: messageSuccess,
            // icon: "success",
          // });

          this.data_s = res.data.processed.applicant;
          this.data_sf = res.data.failed.applicant;

          // process success data return
          var datasuccessarr = [];
          this.data_s.forEach(function (value, key) {
            var valueOb = value[key];
            datasuccessarr[value] = value;
          });

          // process failed data return
          var dataarr = [];
          this.data_sf.forEach(function (value, key) {
            for(key in value) {
              if(value.hasOwnProperty(key)) {
                var valueOb = value[key];
                dataarr[key] = valueOb;
              }
            }
          });
          this.success_data = datasuccessarr;
          this.sfail_data = dataarr;
        }
      },
      (error) => {
        // Swal.fire({
          // title: "Something went wrong", 
          // html: error.error.errors,
          // icon: "error",
        // });

        this.alert_message = error.error.errors;
        this.alert_d = true;
        console.log(error.error.errors);
      }
    );
  }

  pageRefresh() {
    this.pageLoad(this.status);
  }

  uploadDisbursementFile(input) {
    this.apiService.postExcelDisbursement(input).subscribe(
      (res: any) => {
        console.log(res);

        this.isSuccess = res.success;
        const messageSuccess = "Upload Disbursement Success";
        console.log(messageSuccess);
        if (this.isSuccess == true) {
          Swal.fire({
            title: "Upload Disbursement Success",
            html: "Success",
            icon: "success",
          });
          this.totalDisbursementFailed = res.data.failed.total;
          if (this.totalDisbursementFailed > 0) {
            this.disbursementFailed = res.data.failed.applicant;
          }
        }
      },
      (error) => {
        Swal.fire({
          title: "Something went wrong",
          html: error.error.errors,
          icon: "error",
        });
        console.log(error.error.errors);
      }
    );
  }

  checkApp(item: any) {
    if (this.applicant_id.find((x) => x == item.detail_id)) {
      //kalo ada
      // console.log("pop");
      this.applicant_id.splice(this.applicant_id.indexOf(item.detail_id), 1);
    } else {
      //kalo gak ada
      this.applicant_id.push(item.detail_id);
      // console.log("push");
    }
    console.log(this.applicant_id);
  }

  changeStatus() {
    //hanya kalo status and app detail sudah dipilih
    if (this.applicant_id.length > 0 && this.applicant_status !== undefined) {
      // console.log(this.applicant_status);
      // console.log(this.applicant_id.length);
      if (confirm("Are you sure to change status of this application?")) {
        this.data = {
          status: this.applicant_status,
          detail_id: this.applicant_id,
        };
        this.apiService.SetStatusV2Application(this.data).subscribe(
          (response: any) => {
            console.log(response);
            this.applicant_id = [];
            Swal.fire("Success", response.message, "info");
            //this.status = this.applicant_status;
            this.ngOnInit(); // sementara refresh ke page utama, ntar kalo perlu bikin route baru per bucket
            // this.router.navigate(["/application"]);
          },
          (error) => {
            console.log(error);
          }
        );
      }
    }
  }

  onPagination() {
    this.paginationList = [];
    this.paginationList.push(1);

    if (this.current_page > 3) {
      this.paginationList.push("...");
    }

    if (this.current_page > 2) {
      this.paginationList.push(this.current_page - 1);
    }

    if (this.current_page > 1) {
      this.paginationList.push(this.current_page);
    }

    if (this.current_page < this.lastPage) {
      this.paginationList.push(this.current_page + 1);
    }

    if (this.current_page < this.lastPage - 2) {
      this.paginationList.push("..");
    }

    if (this.current_page < this.lastPage - 1) {
      this.paginationList.push(this.lastPage);
    }
  }

  getInitList(data) {
    this.dataLoaded = false;
    this.getListApplication(data);
  }

  getPage(item) {
    this.data["page"] = item;
    this.getListApplication(this.data);
  }

  getListApplication(data) {
    console.log(data.status);
    if (data.status == 'internal_reviewed' || data.status == 'rejected' || data.status == 'processed' || data.status == 'uncontactable' || data.status == 'not_interested') {
      this.assidq = true;
    } else {
      this.assidq = false;
    }
    console.log('assidq : '+this.assidq);
    this.checkapp = this.apiService
      .listV2Application(data)
      .subscribe((response: any) => {
        this.applicant = response.data.data;
        this.sla_aging = response.data.data.sla_aging;
        console.log(this.sla_aging);
        if (this.applicant.length > 0) {
          this.dataLoaded = true;
        } else {
          this.dataLoaded = false;
        }
        this.current_page = response.data.current_page;
        this.lastPage = response.data.last_page;
        this.total = response.data.total;
        this.app_count = response.data.total;
        this.onPagination();
        this.isLoaded = true;
      });
  }

  listApplied(item: any) {
    this.status = item.code;
    this.applicant_id = [];

    this.data.status = item.code;
    this.statusName = this.buckets.find((x) => x.code == item.code).name;
    this.statusCode = this.buckets.find((x) => x.code == this.status).code;

    localStorage.setItem("curBucket", this.statusCode);

    this.getListApplication(this.data);
    if (this.isGoHalal == false) {
      this.urlExcel =
        this.apiService.apiURL +
        "bank-crm/application/export/list-application?status=" +
        this.status +
        "&startDate=&endDate=" +
        this.thisDay +
        "&token=" +
        this.token;
    } else {
      this.urlExcel =
        this.apiService.apiURL +
        "v2/gohalal/excel/export/?status=" +
        this.status +
        "&startDate=&endDate=" +
        this.thisDay +
        "&token=" +
        this.token;
    }
  }

  onDateSelection(date: NgbDate) {
    if (!this.fromDate && !this.toDate) {
      this.fromDate = date;
    } else if (this.fromDate && !this.toDate && date.after(this.fromDate)) {
      this.toDate = date;
    } else {
      this.toDate = null;
      this.fromDate = date;
    }
  }

  isHovered(date: NgbDate) {
    return (
      this.fromDate &&
      !this.toDate &&
      this.hoveredDate &&
      date.after(this.fromDate) &&
      date.before(this.hoveredDate)
    );
  }

  isInside(date: NgbDate) {
    return date.after(this.fromDate) && date.before(this.toDate);
  }

  isRange(date: NgbDate) {
    return (
      date.equals(this.fromDate) ||
      date.equals(this.toDate) ||
      this.isInside(date) ||
      this.isHovered(date)
    );
  }

  validateInput(currentValue: NgbDate, input: string): NgbDate {
    const parsed = this.formatter.parse(input);
    return parsed && this.calendar.isValid(NgbDate.from(parsed))
      ? NgbDate.from(parsed)
      : currentValue;
  }

  onFilterDateRange() {
    this.startDate = this.fromDateElement.nativeElement.value;
    this.endDate = this.toDateElement.nativeElement.value;
    const start = new Date(this.startDate);
    const end = new Date(this.endDate);
    if (isNaN(start.getTime())) {
      this.filterMessage = "Please choose the Start Date";
      return "";
    }
    if (isNaN(end.getTime())) {
      this.filterMessage = "Please choose the End Date";
      return "";
    }
    this.page = 1;
    this.isLoading = true;
    this.data = {
      status: this.status,
      start_date: this.startDate,
      end_date: this.endDate,
      page: this.page,
    };
    this.applicant_id = [];
    this.getListApplication(this.data);

    // tslint:disable-next-line: max-line-length
    if (this.isGoHalal == false) {
      this.urlExcel =
        this.apiService.apiURL +
        "bank-crm/application/export/list-application?status=" +
        this.status +
        "&startDate=" +
        this.startDate +
        "&endDate=" +
        this.endDate +
        "&token=" +
        this.token;
    } else {
      this.urlExcel =
        this.apiService.apiURL +
        "v2/gohalal/excel/export/?status=" +
        this.status +
        "&startDate=" +
        this.startDate +
        "&endDate=" +
        this.endDate +
        "&token=" +
        this.token;
    }
  }

  onButtonExcel() {
    this.activityLog(
      "Download Excel",
      this.userId,
      this.fullName,
      this.bankCode,
      this.accessToken,
      this.bankName,
      this.urlExcel
    );
  }

  activityLog(
    activity,
    userID,
    fullName,
    bankCode,
    accessToken,
    bankName,
    urlExcel
  ) {
    this.datalog = {
      application_name: "Bank CRM",
      activity_name: activity,
      username: userID,
      full_name: fullName,
      access_token: accessToken,
      bank_name: bankName,
      bank_code: bankCode,
      activity_link: urlExcel,
      ip_address: "",
    };
    this.apiService.postActivityLog(this.datalog).subscribe(
      (res: any) => {
        this.error = false;
        this.message = res.message;
      },
      (error) => {
        this.error = true;
        this.message = "Failed to post activity log.";
        console.log("There's an error: " + JSON.stringify(error));
      }
    );
  }

  selectAll() {
    this.applicant_id = [];
    for (var i = 0; i < this.applicant.length; i++) {
      this.applicant[i].selected = this.selectedAll;
      if (this.selectedAll) {
        this.applicant_id.push(this.applicant[i].detail_id);
      }
    }
    console.log(this.applicant_id);
  }

  checkIfAllSelected() {
    this.selectedAll = this.applicant.every(function (item: any) {
      return item.selected == true;
    });
  }

  GsSearch() {
    this.router.navigate([
      "/global-search/" + this.GsCategorySrc + "/" + this.GsSearchText,
    ]);
  }
}
