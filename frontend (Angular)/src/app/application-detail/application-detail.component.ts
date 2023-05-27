import { Component, OnInit, OnDestroy, Output } from "@angular/core";
import Swal from "sweetalert2";
import { ApiService } from "../api.service";
import { Router, ActivatedRoute, Params } from "@angular/router";
import { NgbDropdownConfig } from "@ng-bootstrap/ng-bootstrap";
import { DomSanitizer, SafeResourceUrl } from "@angular/platform-browser";
import { isNumber } from "util";

@Component({
  selector: "app-application-detail",
  templateUrl: "./application-detail.component.html",
  styleUrls: ["./application-detail.component.css"],
  providers: [NgbDropdownConfig],
})
export class ApplicationDetailComponent implements OnInit, OnDestroy {
  bankCode: string;
  applicantId: string;
  emandateId: string;
  applicationId: string;
  applicationStatus: any;
  getTawarruqStatus = false;
  data = {};
  eMandatemessage = "";
  error = false;
  usergroup = "";
  dataApp: any;
  AppStatus: any;
  status = "Pre-DSR / Scoring";
  isLoaded = false;
  notFound = false;
  amlaFound = false;
  result = false;
  isIdDetail = false;
  applicant_status = "";
  isGoHalal: boolean;
  curBucket: any;
  assidq: boolean;
  buckets = [
    { name: "Internal Reviewed", code: "internal_reviewed" },
    { name: "Verified OTP", code: "applied" },
    { name: "Processed", code: "processed" },
    { name: "Uncontactable", code: "uncontactable" },
    { name: "Not Interested", code: "not_interested" },
    { name: "Approved", code: "approved" },
    { name: "Rejected", code: "rejected" },
  ];

  sector = [
    { name: "Agensi Kerajaan", val: "glc" },
    { name: "Pejawat Awam", val: "government" },
    { name: "Pejawat Swasta ", val: "private" },
  ];

  purpose = [
    { name: "Ubah Suai Rumah", val: "home_improvement" },
    { name: "Menyatukan Hutang", val: "debt_consolidation" },
    { name: "Perkahwinan", val: "wedding" },
    { name: "Melancong", val: "vocation" },
    { name: "Penjelasan Kemudahan", val: "facility_settlement " },
    { name: "Lain Lain", val: "other" },
  ];

  docs = [
    { type: "epf", name: "EPF Statement", mime: "", url: "" },
    { type: "ic_back", name: "IC Back Side", mime: "", url: "" },
    { type: "ic_front", name: "IC Front Side", mime: "", url: "" },
    { type: "payslip", name: "Payslip", mime: "", url: "" },
    { type: "tnb_bill", name: "Electricity Bill", mime: "", url: "" },
    { type: "water_bill", name: "Water Bill", mime: "", url: "" },
  ];
  
  getTawarruqArr = ["ghf_tawarruq_in_progress", "ghf_tawarruq_complete", "ghf_tawarruq_keep", "ghf_tawarruq_no", "ghf_confirm_disbursement", "ghf_fwd_approved", "ghf_fwd_rejected"];

  ccrisRep: any;
  ccrisURL: SafeResourceUrl;

  documents = [];

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
    console.log(icNo);

    return this.pad(icNo, 12);
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

  mappingStatus(status) {
    if (this.sector.find((x) => x.val == status)) {
      return this.sector.find((x) => x.val == status).name;
    } else {
      return status;
    }
  }

  mappingPurpose(purpose) {
    if (this.purpose.find((x) => x.val == purpose)) {
      return this.purpose.find((x) => x.val == purpose).name;
    } else {
      return purpose;
    }
  }

  ngOnInit() {
    this.assidq = false;
    this.isGoHalal = false;
    this.curBucket = '';
    if (localStorage.getItem("is_gohalal") == "1") {
      this.isGoHalal = true;
    }
    this.curBucket = localStorage.getItem('curBucket');
    this.status = "Credit Scoring & Report";
    // this.bankCode = localStorage.getItem("bankCode");
    this.applicantId = this.route.snapshot.params.id;
    this.emandateId = this.route.snapshot.params.app_id;
    this.applicationId = this.route.snapshot.params.app_code;
    this.ccrisRep = "";
    this.applicationStatus = this.route.snapshot.params.status;
    // this.detail_id = this.applicantId;
    this.isIdDetail = isNumber(+this.applicantId);
    let doc_sent = [];
    this.docs.forEach((element) => {
      doc_sent.push(element.type);
    });
    if(this.getTawarruqArr.includes(this.applicationStatus)) {
      this.getTawarruqStatus = true;
    }
    console.log(doc_sent);
    console.log('current bucket:'+this.curBucket);

    if (this.curBucket =='internal_reviewed' || this.curBucket =='rejected' || this.curBucket =='processed' || this.curBucket =='uncontactable' || this.curBucket =='not_interested') {
      this.assidq = true;
    }

    if (this.isGoHalal && this.assidq == false) {
      this.data = {
        id: this.route.snapshot.params.app_id,
      };
      this.apiService.getDetailV2GohalalApplication(this.data).subscribe(
        (response: any) => {
          if (response.success) {
            this.dataApp = response.data;
            this.isLoaded = true;
          } else {
            this.notFound = true;
          }
        },
        (error) => {
          console.log(error);
        }
      );
    } else {
      if (this.isIdDetail) {
        this.data = {
          id: this.applicantId,
          doc: doc_sent,
        };

        this.apiService.getDetailV2Application(this.data).subscribe(
          (response: any) => {
            if (response.success) {
              this.dataApp = response.data;
              this.AppStatus = this.statusApp(response.data.app_status);
              this.applicant_status = response.data.app_status;
              this.isLoaded = true;
              if (response.data.amla_report) {
                this.amlaFound = true;
              }
              this.ccrisURL = this.sanitizer.bypassSecurityTrustResourceUrl(
                this.apiService.V2apiURL +
                  "generate/ccris/" +
                  this.applicationId
              );

              this.docs.forEach((element) => {
                element["mime"] =
                  response.data.user_documents[element.type]["mime"];
                element["url"] =
                  response.data.user_documents[element.type]["url"];
              });
              console.log(this.docs);
            } else {
              this.notFound = true;
            }
          },
          (error) => {
            console.log(error);
          }
        );
      }
    }
  }

  ngOnDestroy() {
    this.applicantId = "";
    this.data = {};
  }

  scoringApplied(status) {
    switch (status) {
      case 1:
        this.status = "Credit Scoring & Report";
        break;
      case 2:
        this.status = "Financing";
        break;
      case 3:
        this.status = "Documents";
        break;
      case 4:
        this.status = "E-KYC";
        break;
      case 5:
        this.status = "Approval";
        break;
      default:
        this.status = "Pre-DSR / Scoring";
    }
  }

  getTawarruq() {
    this.data = {
      username: localStorage.getItem("user_id"),
    };
    this.apiService.getTawarruqToken(this.data).subscribe(
      (response: any) => {
        console.log(response);
        if(response.data.status_desc == "SUCCESS") {
          let url = this.apiService.goHalalTawarruq+"?ref="+this.emandateId+"&branch_code=0&username="+response.data.username+"&authkey="+response.data.authkey+"&usergroup=admin";
          window.open(url, '_blank');
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }

  confirmCash() {
    if (confirm("Are you sure to change status of this application?")) {
      this.data = {
        status: 'ghf_tawarruq_complete',
        detail_id: this.applicantId,
        app_id: this.route.snapshot.params.app_id,
        confirm_status: 'CONFIRM_PROCEED',
        user_id: localStorage.getItem('user_id'),
      };
      this.apiService.ConfirmStatusV2Application(this.data).subscribe(
        (response: any) => {
          console.log(response);
          if (response.success){
            Swal.fire("Success", response.message, "info");
            this.router.navigate(["/application"]);
          } else {
            Swal.fire("Error", response.message, "info");
          }
        },
        (error) => {
          console.log(error);
        }
      );
    }
  }

  confirmCancel() {
    if (confirm("Are you sure to change status of this application?")) {
      this.data = {
        status: 'ghf_tawarruq_no',
        detail_id: this.applicantId,
        app_id: this.route.snapshot.params.app_id,
        confirm_status: 'CONFIRM_CANCEL',
        user_id: localStorage.getItem('user_id'),
      };
      this.apiService.ConfirmStatusV2Application(this.data).subscribe(
        (response: any) => {
          console.log(response);
          Swal.fire("Success", response.message, "info");
          this.router.navigate(["/application"]);
        },
        (error) => {
          console.log(error);
        }
      );
    }
  }

  changeStatus() {
    if (confirm("Are you sure to change status of this application?")) {
      this.data = {
        status: this.applicant_status,
        detail_id: [this.applicantId],
      };
      this.apiService.SetStatusV2Application(this.data).subscribe(
        (response: any) => {
          console.log(response);
          Swal.fire("Success", response.message, "info");
          this.router.navigate(["/application"]);
        },
        (error) => {
          console.log(error);
        }
      );
    }
  }

  async onReqMandate() {
    const { value: formValues } = await Swal.fire({
      title: "Request eMandate",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Confirm request eMandate",
      html:
        `
        <div class="row">
          <div class="col applicant-form">
            <div class="p-10">
              Installment Amount (RM) : &nbsp;<input type="number" id="amount" class="swal2-input">
            </div>
          </div>
        </div>` +
        `
        <div class="row">
        <div class="col applicant-form">
          <div class="p-10">
            Frequency : &nbsp;

            <select id="frequency" class="swal2-select">
              <option value="monthly">Monthly</option>
              <option value="weekly">Weekly</option>
              <option value="yearly">Yearly</option>
            </select>

          </div>
        </div>
        </div>
        ` +
        `
        <div class="row">
          <div class="col applicant-form">
            <div class="p-10">
              Max Frequency : &nbsp; <input type="number" id="maxFrequency" class="swal2-input" value="1">
            </div>
          </div>
        </div>
        `,
      focusConfirm: false,
      preConfirm: () => {
        const amount = (document.getElementById("amount") as HTMLInputElement)
          .value;
        const frequency = (document.getElementById(
          "frequency"
        ) as HTMLInputElement).value;
        const maxFrequency = (document.getElementById(
          "maxFrequency"
        ) as HTMLInputElement).value;

        return [amount, frequency, maxFrequency];
      },
    });

    if (formValues) {
      const amount = formValues[0];
      const frequency = formValues[1].toUpperCase();
      const max = formValues[2];
      const bankCode = localStorage.getItem("bank_code");
      const applicantId = this.emandateId;
      const applicationId = this.applicationId;

      this.data = {
        application_id: applicationId,
        applicant_id: applicantId,
        installment_amount: amount,
        frequency,
        max_frequency: max,
        bank_code: bankCode,
      };
      // tslint:disable-next-line: max-line-length
      this.apiService.bankRequestEmandate(this.data).subscribe(
        (res: any) => {
          this.error = false;
          this.eMandatemessage = res.message;
          // this.router.navigate(['/application-detail', this.applicantId]);
        },
        (error) => {
          this.error = true;
          this.eMandatemessage =
            "Failed to send email, please fill in the form correctly";
          console.log("There's an error: " + JSON.stringify(error));
        }
      );
    }
  }
}
