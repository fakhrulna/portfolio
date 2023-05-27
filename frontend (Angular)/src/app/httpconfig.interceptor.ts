import { Injectable } from "@angular/core";
import Swal from "sweetalert2";
import { Router } from "@angular/router";
import {
  HttpInterceptor,
  HttpRequest,
  HttpResponse,
  HttpHandler,
  HttpEvent,
  HttpErrorResponse,
} from "@angular/common/http";

import { Observable, throwError } from "rxjs";
import { map, catchError } from "rxjs/operators";
import { environment } from "../environments/environment";

@Injectable()
export class HttpConfigInterceptor implements HttpInterceptor {
  constructor(private route: Router) {}

  intercept(
    request: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    const token = localStorage.getItem("token");

    if (token) {
      request = request.clone({
        headers: request.headers.set("Authorization", "Bearer " + token),
      });
    }

    if (!request.headers.has("Content-Type")) {
      // FIXME: This part causes requests with multipart-form type forced into application/json
      // request = request.clone({headers: request.headers.set('Content-Type', 'application/json')});
    }

    request = request.clone({
      headers: request.headers.set("Accept", "application/json"),
    });

    // request = request.clone({
    //   headers: request.headers.set(
    //     "Access-Control-Allow-Headers",
    //     "Content-Type"
    //   ),
    // });

    // request = request.clone({
    //   headers: request.headers.set(
    //     "Access-Control-Allow-Methods",
    //     "GET, POST, OPTIONS"
    //   ),
    // });

    // request = request.clone({
    //   headers: request.headers.set("Access-Control-Allow-Origin", "*"),
    // });

    return next.handle(request).pipe(
      map((event: HttpEvent<any>) => {
        return event;
      }),
      catchError((error: HttpErrorResponse) => {
        let errorMsg = "Unknown Error!";

        if (error.error.message != 'uploaderror') {
          if (!environment.production) {
            errorMsg = "Unknown Error, Please check server response in network";
          }

          // Catch if server error
          if (!error.status || error.status === 0) {
            Swal.fire(
              "Server Error",
              "Failed Connecting To Server, Please Try Again Later",
              "error"
            );
            return throwError(errorMsg);
          }

          // Catch error that contain body response
          if (!error.error.success) {
            if (error.error.message) {
              errorMsg = error.error.message;
            }

            //checking if errors is tring or object
            if (typeof error.error.errors === 'string' || error.error.errors instanceof String) { 
              Swal.fire({
                title: "Error!",
                html: error.error.errors,
              });

              return throwError(error);
            } else {
              // Catch validation errors
              if (error.error.errors) {
                errorMsg = "";
                Object.keys(error.error.errors).forEach(function (key) {
                  let errorItem = key.charAt(0).toUpperCase() + key.slice(1);
                  let errorCause = "";
                  let errorLists = error.error.errors[key];
                  for (var prop in errorLists) {
                    errorCause += "<li>" + errorLists[prop] + "</li>";
                  }

                  errorMsg +=
                    "<ul><b>" + errorItem + "</b>" + errorCause + "</ul><br>";
                });

                Swal.fire({
                  title: "Error!",
                  html: errorMsg,
                });

                return throwError(error);
              }
            }

            // Redirect if session expired
            if (error.status === 401) {
              localStorage.clear();
              this.route.navigate(["/login"]);
            }

            // Catch if endpoint not found
            if (error.status === 404) {
              errorMsg = "Endpoint Resource Not Found!";
            }
          }

          Swal.fire("Ooops!", errorMsg, "error");
        }

        return throwError(error);
      })
    );
  }
}
