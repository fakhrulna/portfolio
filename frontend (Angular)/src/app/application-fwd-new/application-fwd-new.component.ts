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
import { ActivatedRoute, Router } from "@angular/router";
import { FormGroup } from "@angular/forms";

@Component({
  selector: "app-application-fwd-new",
  templateUrl: "./application-fwd-new.component.html",
  styleUrls: ["./application-fwd-new.component.css"],
  providers: [NgbDropdownConfig],
})
export class ApplicationFwdNewComponent implements OnInit {
  @ViewChild("dpFromDate", { static: false }) fromDateElement: ElementRef;
  @ViewChild("dpToDate", { static: false }) toDateElement: ElementRef;
  buckets = [
    { name: "FWD Approved", code: "ghf_fwd_approved" },
    { name: "FWD Rejected", code: "ghf_fwd_rejected" },
    { name: "FWD Exceptional Report", code: "exceptional" },
  ];
  applicant_status: any;
  applicant_id = [];

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
  filterStatus = ["All", "Completed", "Pending"];
  selectFilterStatus = "All";

  constructor(
    private apiService: ApiService,
    private calendar: NgbCalendar,
    public formatter: NgbDateParserFormatter,
    config: NgbDropdownConfig,
    private route: ActivatedRoute,
    private router: Router
  ) {
    config.placement = "bottom-left";
    config.autoClose = true;
    this.fromDate = "";
    this.toDate = "";
  }

  ngOnInit() {
    if (this.route.snapshot.params.status) {
      this.status = this.route.snapshot.params.status;
    } else {
      this.status = "ghf_fwd_approved";
    }
    this.pageLoad(this.status);
  }

  pageLoad(pageStatus) {
    this.page = this.page ? this.page : 1;
    this.status = pageStatus;
    this.applicant_id = [];

    this.statusName = this.buckets.find((x) => x.code == this.status).name;

    this.data = {
      status: this.status,
    };
    this.getInitList(this.data);

    this.urlExcel =
      this.apiService.apiURL +
      "v2/gohalal/excel/export/?status=" +
      this.status +
      "&startDate=&endDate=" +
      this.thisDay +
      "&token=" +
      this.token;
  }

  onExcelPicked(files: FileList) {
    this.fileToUpload = files.item(0);

    let input = new FormData();
    input.append("file", this.fileToUpload);

    console.log(this.excelBulk);
    this.uploadFileToActivity(input);
  }

  uploadFileToActivity(input) {
    this.apiService.postExcelBulk(input).subscribe(
      (res: any) => {
        console.log(res);

        this.isSuccess = res.success;
        const messageSuccess = "Upload Excel Success";
        console.log(messageSuccess);
        if (this.isSuccess == true) {
          Swal.fire({
            title: "Upload Excel Success",
            html: messageSuccess,
            icon: "success",
          });
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

  pageRefresh() {
    this.pageLoad(this.status);
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
    console.log(data);
    this.checkapp = this.apiService
      .listGohalalApplication(data)
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

    this.getListApplication(this.data);
    this.urlExcel =
      this.apiService.apiURL +
      "v2/gohalal/excel/export/?status=" +
      this.status +
      "&startDate=&endDate=" +
      this.thisDay +
      "&token=" +
      this.token;
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
    // console.log(this.selectFilterStatus);
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
      filter_status: this.selectFilterStatus,
      status: this.status,
      start_date: this.startDate,
      end_date: this.endDate,
      page: this.page,
    };
    this.applicant_id = [];
    this.getListApplication(this.data);

    this.urlExcel =
      this.apiService.apiURL +
      "v2/gohalal/excel/export/?status=" +
      this.status +
      "&filter=" +
      this.selectFilterStatus +
      "&startDate=" +
      this.startDate +
      "&endDate=" +
      this.endDate +
      "&token=" +
      this.token;
  }

  onFilterSelected() {
    this.page = 1;
    this.isLoading = true;
    this.data = {
      filter_status: this.selectFilterStatus,
      status: this.status,
    };
    this.applicant_id = [];
    this.getListApplication(this.data);

    this.urlExcel =
      this.apiService.apiURL +
      "v2/gohalal/excel/export/?status=" +
      this.status +
      "&filter=" +
      this.selectFilterStatus +
      "&startDate=" +
      this.startDate +
      "&endDate=" +
      this.endDate +
      "&token=" +
      this.token;
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
}
