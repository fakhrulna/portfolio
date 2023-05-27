import { Component, OnInit, ViewChild } from '@angular/core';
import { ApiService } from '../api.service';
import { ChartDataSets, ChartOptions } from 'chart.js';
import { Label, Color, BaseChartDirective } from 'ng2-charts';
import * as pluginAnnotations from 'chartjs-plugin-annotation';

@Component({
  selector: 'app-analysis',
  templateUrl: './analysis.component.html',
  styleUrls: ['./analysis.component.css']
})
export class AnalysisComponent implements OnInit {
  status: any;
  done: any;

    public lineChartData: ChartDataSets[] = [
      { data: [32, 39, 43, 55, 32, 33, 45, 67, 79, 90], label: 'Total Application' },
      { data: [25, 29, 37, 50, 22, 13, 35, 58, 68, 80], label: 'Total Pre Approved' },
      { data: [6, 9, 2, 0, 2, 1, 2, 1, 3, 2], label: 'Total Rejected' },
      { data: [2, 5, 4, 5, 1, 3, 9, 7,4, 8], label: 'Total Pending' },

    ];
    public lineChartLabels: Label[] = ['January', 'February', 'March', 'April', 'May', 'June', 'July', "Augustus", "September", "October", "November", "December"];
    public lineChartOptions: (ChartOptions & { annotation: any }) = {
      responsive: true,
      scales: {
        xAxes: [{}],
        yAxes: [
          {
            id: 'y-axis-0',
            position: 'left',
          },
        ]
      },
      annotation: {
        annotations: [
        ],
      },
    };
    public lineChartColors: Color[] = [
      { // grey
        backgroundColor: 'rgba(148,159,177,0.2)',
        borderColor: 'rgba(148,159,177,1)',
        pointBackgroundColor: 'rgba(148,159,177,1)',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: 'rgba(148,159,177,0.8)'
      },
      { // dark grey
        backgroundColor: 'rgba(77,83,96,0.2)',
        borderColor: 'rgba(77,83,96,1)',
        pointBackgroundColor: 'rgba(77,83,96,1)',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: 'rgba(77,83,96,1)'
      },
      { // red
        backgroundColor: 'rgba(255,0,0,0.3)',
        borderColor: 'red',
        pointBackgroundColor: 'rgba(148,159,177,1)',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: 'rgba(148,159,177,0.8)'
      }
    ];
    public lineChartLegend = true;
    public lineChartType = 'line';
    public lineChartPlugins = [pluginAnnotations];
  
    @ViewChild(BaseChartDirective, { static: true }) chart: BaseChartDirective;
  
    constructor(
      private api: ApiService,
    ) { 
      console.log('analysis page loaded');
    }

    
    ngOnInit() {
      this.done = false;
      this.loadData();
    }

    loadData() {
      this.done = true;
    }
  
  }
