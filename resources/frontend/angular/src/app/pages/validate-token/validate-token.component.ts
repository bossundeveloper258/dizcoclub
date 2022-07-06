import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
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

  constructor(
    private orderService: OrderService,
    private activatedRoute: ActivatedRoute,
    private storageService: StorageService,
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
      },
      error => {

      }
    )
  }

}
