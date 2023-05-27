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
  selector: "app-global-search",
  templateUrl: "./global-search.component.html",
  styleUrls: ["./global-search.component.css"],
  providers: [NgbDropdownConfig],
})
export class GlobalSearchComponent implements OnInit {
  applicant: any;
  checkapp: any;
  data: any;
  status: any;
  isLoaded = false;
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

  constructor(
    private apiService: ApiService,
    private calendar: NgbCalendar,
    public formatter: NgbDateParserFormatter,
    config: NgbDropdownConfig,
    private router: Router,
    private route: ActivatedRoute
  ) {
    config.placement = "bottom-left";
    config.autoClose = true;
  }

  ngOnInit() {
    this.GsCategorySrc = this.route.snapshot.params.src_id;
    this.GsSearchText = this.route.snapshot.params.src_value;
    this.data = { src_id: this.GsCategorySrc, src_value: this.GsSearchText };
    this.pageLoad();
  }

  pageLoad() {
    this.page = this.page ? this.page : 1;
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

  pageRefresh() {
    this.pageLoad();
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
    console.log(data);

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
      .listGlobalSearchApplication(data)
      .subscribe((response: any) => {
        this.applicant = response.data.data;
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

    this.data.status = item.code;

    localStorage.setItem("curBucket", this.statusCode);

    this.getListApplication(this.data);
    this.urlExcel =
      this.apiService.apiURL +
      "v2/gohalal/excel/export/?startDate=&endDate=" +
      this.thisDay +
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

  GsSearch() {
    this.data = { src_id: this.GsCategorySrc, src_value: this.GsSearchText };
    this.getListApplication(this.data);
    // this.router.navigate([
    //   "/global-search/" + this.GsCategorySrc + "/" + this.GsSearchText,
    // ]);
  }
}
