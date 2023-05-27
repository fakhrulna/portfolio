import { Component, OnInit, OnDestroy, Output } from "@angular/core";
import Swal from "sweetalert2";
import { ApiService } from "../api.service";
import { Router, ActivatedRoute, Params } from "@angular/router";
import { NgbDropdownConfig } from "@ng-bootstrap/ng-bootstrap";
import { DomSanitizer, SafeResourceUrl } from "@angular/platform-browser";
import { isNumber } from "util";

@Component({
  selector: "app-exceptional-detail",
  templateUrl: "./exceptional-detail.component.html",
  styleUrls: ["./exceptional-detail.component.css"],
  providers: [NgbDropdownConfig],
})
export class ExceptionalDetailComponent implements OnInit, OnDestroy {
  bankCode: string;
  appId: string;
  applicationStatus: any;
  data = {};
  error = false;
  usergroup = "";
  dataApp: any;
  AppStatus: any;
  isLoaded = false;
  notFound = false;
  result = false;
  applicant_status = "";
  isGoHalal: boolean;
  newName: any;
  newIcNo: any;

  constructor(
    private sanitizer: DomSanitizer,
    private apiService: ApiService,
    private router: Router,
    private route: ActivatedRoute,
    config: NgbDropdownConfig
  ) {
    config.placement = "bottom-left";
    config.autoClose = true;
  }

  pad(str, max) {
    str = str.toString();
    return str.length < max ? this.pad("0" + str, max) : str;
  }

  icNoShow(icNo) {
    return this.pad(icNo, 12);
  }

  back() {
    this.router.navigate(["/application-fwd/exceptional"]);
  }

  setNewName(data) {
    this.newName = data;
  }

  setNewIcNo(data) {
    this.newIcNo = data;
  }

  saveData() {
    this.data = {
      app_id: this.appId,
      new_name: this.newName,
      new_ic_no: this.newIcNo,
    };
    this.apiService.saveGohalalValidateDetail(this.data).subscribe(
      (response: any) => {
        if (response.success) {
          this.dataApp.exceptional_status = "completed";
          this.dataApp.validated_name = this.newName;
          this.dataApp.validated_ic = this.newIcNo;
          Swal.fire("Success!", "Validation Success", "success");
        } else {
          Swal.fire("Error!", "Validation Error", "error");
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }

  statusApp(status) {
    if (status == "internal_reviewed") return "applied";
    else {
      if (status.substring(0, 3) == "ghf") {
        return status.substring(4, 50);
      } else {
        return status;
      }
    }
  }

  ngOnInit() {
    this.isGoHalal = true;

    this.appId = this.route.snapshot.params.app_id;
    this.data = {
      app_id: this.appId,
    };
    this.apiService.getGohalalValidateDetail(this.data).subscribe(
      (response: any) => {
        if (response.success) {
          this.dataApp = response.data;
          this.newName = response.data.name;
          this.newIcNo = response.data.ic_no;
          this.isLoaded = true;
        } else {
          this.notFound = true;
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }

  ngOnDestroy() {
    this.appId = "";
    this.data = {};
  }
}
