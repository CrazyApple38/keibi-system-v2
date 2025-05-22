<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * 基底Controllerクラス
 * 
 * 全てのControllerの共通機能を提供
 * 認証、ジョブディスパッチ、バリデーション機能を統合
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 成功レスポンスを返す
     * 
     * @param mixed $data レスポンスデータ
     * @param string $message メッセージ
     * @param int $statusCode HTTPステータスコード
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data = null, string $message = '処理が完了しました', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * エラーレスポンスを返す
     * 
     * @param string $message エラーメッセージ
     * @param int $statusCode HTTPステータスコード
     * @param mixed $errors エラー詳細
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = 'エラーが発生しました', int $statusCode = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * ページネーション付きレスポンスを返す
     * 
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @param string $message メッセージ
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginationResponse($paginator, string $message = 'データ取得が完了しました')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ]);
    }
}
