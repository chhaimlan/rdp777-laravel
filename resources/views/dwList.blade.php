@extends('layouts.app2')
@section('content')
     <p class="tc fs-2 pt-2">HISTORY</p>
        <div class="container report">
            <div class="button-3">
                <a href="#"><input class="vx klavika" type="button" value="Transfer"></a>
                <a href="{{ route('bonus-list') }}"><input class="vz bg-black klavika" type="button" value="Bonus"></a>
            </div>
            <div class="table-2 bg-black">
                <table>
                    <tr class="tc bg-gold">
                        <th>Tanggal</th>
                        <th>Id Transfer</th>
                        <th>Jumlah</th>
                        <th>Tipe Transfer</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                    @foreach ($transferRecords as $trs)
                        <tr class="tc">
                            <td>
                                {{$trs->transfer_time}}
                            </td>
                            <td>
                                {{$trs->transfer_id}}
                            </td>
                            <td>
                                    {{number_format($trs->money_value)}}
                            </td>
                            <td>
                                @if ($trs->type == 0)
                                    Deposit
                                @else
                                    Withdraw
                                @endif
                            </td>
                            <td>
                                @if ($trs->status == 0)
                                    Pending
                                @elseif($trs->status == 1)
                                    Pending
                                @elseif($trs->status == 3)
                                    @if ($trs->type == 0)
                                        Successful
                                    @else
                                        Successful
                                    @endif
                                @else
                                    Failure
                                @endif
                            </td>

                            <td>
                                {{$trs->remark}}
                            </td>
                        </tr>
                    @endforeach
                </table>
                <div style="float: right;display: inline;">
                    {{ $transferRecords->links() }}
                </div>
            </div>
        </div>
          <div class="contact flex">
				<a href="#"><i class="fab fa-whatsapp"></i><h3>Whtasapp</h3>1234567890</a>
				<a href="#"><i class="fab fa-facebook"></i><h3>Facebook</h3>1234567890</a>
				<a href="#"><i class="fab fa-instagram"></i><h3>Instagram</h3>1234567890</a>
				<a href="#"><i class="fab fa-telegram-plane"></i><h3>Telegram</h3>1234567890</a>
				<a href="#"><i class="fab fa-line"></i><h3>Line</h3>1234567890</a>
		</div>
		<div class="content">
			<h2>Title Here</h2>
			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi
			ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
			officia deserunt mollit anim id est laborum.
			<br>
			Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta
			sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem
			ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem
			ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat
			quo voluptas nulla pariatur?
		</div>
@endsection
