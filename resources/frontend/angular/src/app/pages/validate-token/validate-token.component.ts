import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { NzModalService } from 'ng-zorro-antd/modal';
import { OrderService } from 'src/app/core/services/order.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { UserModel } from 'src/app/shared/models/auth.model';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-validate-token',
  templateUrl: './validate-token.component.html',
  styleUrls: ['./validate-token.component.css']
})
export class ValidateTokenComponent implements OnInit {

  ticket: any;
  routeAsset = environment.assetsUrl;
  host = environment.urlHost;
  routeStorage = environment.storageUrl;
  token!: string;

  user!: UserModel;
  loadingBtn: boolean = false;
  loading: boolean = true;

  constructor(
    private orderService: OrderService,
    private activatedRoute: ActivatedRoute,
    private storageService: StorageService,
    private modalService: NzModalService,
    private router: Router,
  ) {
    this.user = this.storageService.getUser();
    this.activatedRoute.paramMap.subscribe( paramMap => {
      this.token = paramMap.get('token') ?? "";
    });
  }

  ngOnInit(): void {
    this.orderService.getTicketByToken( this.token ).subscribe(
      res => {
        this.ticket = res[0];
        this.loading = false;
      },
      error => {
        this.modalService.error({
          nzTitle: "Error",
          nzContent: error.message,
          nzOkText: "Aceptar",
          nzOnOk: () => {
            this.router.navigate(['/']);
          }
        })
      }
    )
  }

  confirm(){
    this.loadingBtn = true;
    this.orderService.postConfirmAssist( this.token ).subscribe(
      res => {
        this.loadingBtn = false;
        this.modalService.success({
          nzTitle: "Confirmación",
          nzContent: "Se confirmo la asistencia",
          nzClosable: false,
          nzOkText: "Aceptar",
          nzOnOk: () => {
            this.router.navigate(['/']);
          }
        })
      },
      error => {
        this.loadingBtn = false;
        this.modalService.error({
          nzTitle: "Error",
          nzContent: error.message
        })
      }
    )
  }
}
