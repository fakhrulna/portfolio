import { Injectable } from "@angular/core";
import { Observable, of } from "rxjs";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { catchError, tap } from "rxjs/operators";
import Swal from "sweetalert2";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";

const httpOptions = {
  headers: new HttpHeaders({ "Content-Type": "application/json" }),
};

@Injectable({
  providedIn: "root",
})
export class ApiService {
  currBaseURL: any;
  token: any;
  result: any;
  apiURL: any;
  V2apiURL: any;
  authDataLogin: any;
  V2apiStaging;
  endpoint: any;
  goHalalTawarruq: any;

  constructor(
    private http: HttpClient,
    private router: Router,
    private platformLocation: PlatformLocation
  ) {
    this.checkBaseUrl();
  }

  createAuthorizationHeader(headers: Headers) {
    const token = localStorage.getItem("access_token");
    headers.append("Authorization", "Bearer " + token);
  }

  checkBaseUrl() {
    this.currBaseURL = (this.platformLocation as any).location.origin;

    this.apiURL = "http://localhost/assidq-av2/";
    this.V2apiURL = "http://localhost/assidq-av2/v2/";
  }

  private handleError<T>(result?: T) {
    return (error: any): Observable<T> => {
      console.error(error.error.error_description);
      if (
        error.error.error_description == "The access token provided has expired"
      ) {
        localStorage.setItem("access_token", null);
        this.router.navigate(["/login"]);
      }
      return of(result as T);
    };
  }

  sendLogin(data): Observable<any> {
    return this.http.post<any>(this.apiURL + "gettoken", data, httpOptions);
  }

  changePassword(data): Observable<any> {
    return this.http.post<any>(
      this.V2apiURL + "bank-crm/change-password",
      data,
      httpOptions
    );
  }

  getApplicationCount(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const usergroup = localStorage.getItem("bank_code");
    return this.http
      .post<any>(
        this.apiURL + "resource/User/GetApplicationCount",
        {
          usergroup: usergroup,
          status: data.status,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  getApplicationSum(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const usergroup = localStorage.getItem("bank_code");
    return this.http
      .post<any>(
        this.apiURL + "resource/User/GetApplicationSum",
        {
          usergroup: usergroup,
          status: data.status,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  listApplication(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const usergroup = localStorage.getItem("bank_code");
    return this.http
      .post<any>(
        this.apiURL + "resource/User/ListApplication",
        {
          usergroup: usergroup,
          start: 0,
          total: 1000,
          status: data.status,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  listV2Application(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    let data_sent = {
      token: token,
      status: data.status,
    };
    if (data.start_date) {
      data_sent["start_date"] = data.start_date;
    }
    if (data.end_date) {
      data_sent["end_date"] = data.end_date;
    }
    if (data.page) {
      data_sent["page"] = data.page;
    }
    if (localStorage.getItem("is_gohalal") == "1") {
      return this.http
        .post<any>(this.V2apiURL + "gohalal/crm/list-application", data_sent)
        .pipe(catchError(this.handleError("ERROR")));
    } else {
      return this.http
        .post<any>(this.V2apiURL + "bank-crm/list-application", data_sent)
        .pipe(catchError(this.handleError("ERROR")));
    }
  }

  listGlobalSearchApplication(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    let data_sent = {
      token: token,
      src_id: data.src_id,
      src_value: data.src_value,
    };
    if (data.start_date) {
      data_sent["start_date"] = data.start_date;
    }
    if (data.end_date) {
      data_sent["end_date"] = data.end_date;
    }
    if (data.page) {
      data_sent["page"] = data.page;
    }
    return this.http
      .post<any>(this.V2apiURL + "gohalal/crm/gs-list-application", data_sent)
      .pipe(catchError(this.handleError("ERROR")));
  }

  listGohalalApplication(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const header = {
      headers: new HttpHeaders().set("Authorization", `Bearer ${token}`),
    };
    let data_sent = {
      status: data.status,
    };
    if (data.start_date) {
      data_sent["start_date"] = data.start_date;
    }
    if (data.end_date) {
      data_sent["end_date"] = data.end_date;
    }
    if (data.page) {
      data_sent["page"] = data.page;
    }
    if (data.filter_status) {
      data_sent["filter_status"] = data.filter_status;
    }
    return this.http
      .post<any>(
        this.V2apiURL + "gohalal/crm/list-application",
        data_sent,
        header
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  getDetailV2Application(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    let data_sent = {
      token: token,
      id: data.id,
      doc: data.doc,
    };
    return this.http
      .post<any>(this.V2apiURL + "bank-crm/applicant/detail", data_sent)
      .pipe(catchError(this.handleError("ERROR")));
  }

  getDetailV2GohalalApplication(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    let data_sent = {
      id: data.id,
    };
    return this.http
      .post<any>(this.V2apiURL + "gohalal/crm/detail", data_sent, {
        headers: new HttpHeaders({
          "Content-Type": "application/json",
          Authorization: "Bearer " + token,
        }),
      })
      .pipe(catchError(this.handleError("ERROR")));
  }

  SetStatusV2Application(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    let data_sent = {
      token: token,
      detail_id: data.detail_id,
      status: data.status,
    };
    return this.http
      .post<any>(this.V2apiURL + "bank-crm/applicant/change-status", data_sent)
      .pipe(catchError(this.handleError("ERROR")));
  }

  ConfirmStatusV2Application(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    let data_sent = {
      token: token,
      detail_id: data.detail_id,
      status: data.status,
      app_id: data.app_id,
      confirm_status: data.confirm_status,
      user_id: data.user_id,
    };
    return this.http
      .post<any>(this.V2apiURL + "gohalal/crm/confirm-change-status", data_sent)
      .pipe(catchError(this.handleError("ERROR")));
  }

  latestApplication(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const usergroup = localStorage.getItem("bank_code");
    return this.http
      .post<any>(
        this.apiURL + "resource/User/ListApplication",
        {
          usergroup: usergroup,
          days: data.days,
          latest_app: true,
          status: data.status,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  getDetailApplication(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const usergroup = localStorage.getItem("bank_code");
    return this.http
      .post<any>(
        this.apiURL + "resource/User/ListApplication",
        {
          usergroup: usergroup,
          status: data.status,
          appDetailId: data.appId,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  getApplication(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const usergroup = localStorage.getItem("bank_code");
    return this.http
      .post<any>(
        this.apiURL + "resource/User/ListApplication",
        {
          usergroup: usergroup,
          status: data.status,
          appId: data.appId,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  getAppDocList(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    console.log("GetAppDocList (API): " + JSON.stringify(data));
    return this.http
      .post<any>(
        this.apiURL + "resource/User/GetAppDocList",
        {
          app_id: data.app_id,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(
        tap((data) => console.log("GetAppDocList (loaded)")),
        catchError(this.handleError("ERROR"))
      );
  }

  listProduct(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const usergroup = localStorage.getItem("bank_code");

    return this.http
      .post<any>(this.V2apiURL + "bank-crm/produk/pf", data, {
        headers: new HttpHeaders({
          "Content-Type": "application/json",
          Authorization: "Bearer " + token,
        }),
      })
      .pipe(catchError(this.handleError("ERROR")));
  }

  getProductDetails(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    console.log("getArticleDetails (API): " + JSON.stringify(data));
    console.log(this.apiURL + "resource/User/GetProduct");
    return this.http
      .post<any>(
        this.apiURL + "resource/User/GetProduct",
        {
          product_code: data.id,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(
        tap((data) => console.log("GetProductDetails (loaded)")),
        catchError(this.handleError("ERROR"))
      );
  }

  getListApplicationByDate(
    startDate,
    endDate,
    status = "applied",
    token
  ): Observable<any> {
    return this.http
      .post<any>(
        this.apiURL + "bank-crm/list-application",
        {
          start_date: startDate,
          end_date: endDate,
          status,
          token,
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  postActivityLog(data) {
    return this.http.post(this.apiURL + "audit-trail/activityLog", data);
  }

  getUser(data): Observable<any> {
    function searchIndex(element) {
      return element === "access_token";
    }
    const find = data.findIndex(searchIndex);
    const token = localStorage.getItem(data[find]);

    return this.http
      .post<any>(
        this.apiURL + "resource/User/ListProduct",
        {
          usergroup: "admin",
        },
        {
          headers: new HttpHeaders({
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          }),
        }
      )
      .pipe(catchError(this.handleError("ERROR")));
  }

  bankRequestEmandate(data) {
    return this.http.post(this.apiURL + "curlec/emandate/generate", data);
  }

  postExcelBulk(data) {
    // console.log(fileToUpload.name);
    const token = localStorage.getItem("access_token");
    const is_gohalal = localStorage.getItem("is_gohalal");
    if (is_gohalal == "1") {
      this.endpoint = this.V2apiURL + "gohalal/crm/bulk-upload";
    } else {
      this.endpoint = this.V2apiURL + "bank-crm/applicant/bulk-upload";
    }
    let headers = new HttpHeaders();
    headers = headers.set("Authorization", "Bearer " + token);

    return this.http.post(this.endpoint, data, { headers: headers });
  }

  postExcelDisbursement(data) {
    // console.log(fileToUpload.name);
    const token = localStorage.getItem("access_token");
    this.endpoint = this.V2apiURL + "gohalal/crm/disbursed-upload";
    let headers = new HttpHeaders();
    headers = headers.set("Authorization", "Bearer " + token);

    return this.http.post(this.endpoint, data, { headers: headers });
  }
  
  authData(data) {
    return (this.authDataLogin = data);
    console.log("dari api service" + JSON.stringify(data));
  }

  getGohalalDashboard(): Observable<any> {
    const token = localStorage.getItem("access_token");
    const header = {
      headers: new HttpHeaders().set("Authorization", `Bearer ${token}`),
    };
    return this.http
      .get(this.V2apiURL + "gohalal/crm/dashboard", header)
      .pipe(catchError(this.handleError("ERROR")));
  }

  getGohalalValidateDetail(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const header = {
      headers: new HttpHeaders().set("Authorization", `Bearer ${token}`),
    };
    return this.http
      .post(this.V2apiURL + "gohalal/crm/detail-validate", data, header)
      .pipe(catchError(this.handleError("ERROR")));
  }

  saveGohalalValidateDetail(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const header = {
      headers: new HttpHeaders().set("Authorization", `Bearer ${token}`),
    };
    return this.http
      .post(this.V2apiURL + "gohalal/crm/save-validate", data, header)
      .pipe(catchError(this.handleError("ERROR")));
  }

  getTawarruqToken(data): Observable<any> {
    const token = localStorage.getItem("access_token");
    const header = {
      headers: new HttpHeaders().set("Authorization", `Bearer ${token}`),
    };
    return this.http
      .post(this.V2apiURL + "gohalal/crm/tawarruq-token", data, header)
      .pipe(catchError(this.handleError("ERROR")));
  }
}
