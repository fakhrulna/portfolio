import { Component, OnInit } from "@angular/core";
import { Router } from "@angular/router";
import { ApiService } from "../api.service";

@Component({
  selector: "app-home",
  templateUrl: "./home.component.html",
  styleUrls: ["./home.component.css"],
})
export class HomeComponent implements OnInit {
  checklogin: any;
  checkapi: any;
  total_applicant: number;
  total_unprocessed_applicant: number = 0;
  total_loan_value: number = 0;
  data_loaded = false;
  latest_applicant = [];
  now = new Date();
  startDate: any;
  endDate = this.now.toJSON().slice(0, 10).replace(/-/g, "-");

  paginationList: any;
  current_page: any;
  lastPage: any;
  total: any;
  app_count: any;
  paging = false;
  isGoHalal = false;
  ghf_reviewed = 0;
  ghf_fwd_approved = 0;
  ghf_tawarruq_complete = 0;
  ghf_tawarruq_in_progress = 0;
  ghf_rejected = 0;

  constructor(private router: Router, private apiService: ApiService) {}

  ngOnInit() {
    this.isGoHalal = localStorage.getItem("is_gohalal") == "1";
    if (!this.isGoHalal) {
      this.startDate = new Date(this.now.setDate(this.now.getDate() - 3))
        .toJSON()
        .slice(0, 10)
        .replace(/-/g, "-");
      console.log(this.startDate);
      console.log(this.endDate);

      console.log("home loaded");
      let data_latest = {
        status: "internal_reviewed",
        start_date: this.startDate,
        end_date: this.endDate,
      };
      console.log("latest app loaded");
      this.checkapi = this.apiService
        .listV2Application(data_latest)
        .subscribe((response: any) => {
          console.log(response.data);
          this.latest_applicant = response.data.data;
          if (this.latest_applicant.length > 0) {
            this.data_loaded = true;
          } else {
            this.data_loaded = false;
          }
          this.current_page = response.data.current_page;
          this.lastPage = response.data.last_page;
          this.total = response.data.total;
          this.app_count = response.data.total;
        });
      this.total_applicant = 0;
      this.total_unprocessed_applicant = 0;
      this.total_loan_value = 0;
      let data = {
        status: "internal_reviewed",
      };
      this.checkapi = this.apiService
        .getApplicationCount(data)
        .subscribe((response: any) => {
          this.total_applicant = response.data[0].app_count;
        });
      this.checkapi = this.apiService
        .getApplicationSum(data)
        .subscribe((response: any) => {
          this.total_loan_value = response.data[0].loan_amount;
        });
    } else {
      this.checkapi = this.apiService
        .getGohalalDashboard()
        .subscribe((response: any) => {
          this.total_applicant = response.data.total_app;
          this.total_loan_value = response.data.total_amount;
          this.ghf_reviewed = response.data.ghf_reviewed;
          this.ghf_fwd_approved = response.data.ghf_fwd_approved;
          this.ghf_tawarruq_complete = response.data.ghf_tawarruq_complete;
          this.ghf_tawarruq_in_progress =
            response.data.ghf_tawarruq_in_progress;
          this.ghf_rejected = response.data.ghf_rejected;
          // this.total_applicant = response.data[0].app_count;
        });
    }
  }

  mappingStatus(pencarian: any, status) {
    // return pencarian;
    if (pencarian.find((x) => x.status == status)) {
      return pencarian.find((x) => x.status == status);
    } else {
      return null;
    }
  }
}
