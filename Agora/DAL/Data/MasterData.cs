using Agora.DAL.Query;
using Agora.Models;
using Microsoft.Extensions.Configuration;
using Dapper;
using MySql.Data.MySqlClient;
using System.Data.SqlClient;
using System.Data;

//using System.Text.Json;

namespace Agora.DAL.Data
{
    public interface IMasterData
    {
        public Task<List<Master>> GetMastersAsync();       
        public Task InsertMasterAsync(Master master);
        public Task UpdateMasterAsync(Master master);
        public Task DeleteMasterAsync(Guid IdMaster);
		
		//Option Sector
		public Task<List<MasterOption>> GetMasterOptionAsync(Guid IdMaster);
		public Task InsertOptionAsync(MasterOption[] option);
		public Task UpdateOptionAsync(MasterOption[] option);
        public Task DeleteOptionsByIdMasterAsync(Guid IdMaster);
        public Task DeleteOptionAsync(Guid IdOption);
    }
    public class MasterData : IMasterData
    {
        private readonly IConfiguration _configuration;
        private readonly IDbConnection _connection;
        public MasterData(IConfiguration configuration, IDbConnection connection)
        {
            _configuration = configuration;
            _connection = connection;
            _connection.ConnectionString = _configuration.GetConnectionString("dbSQL");
        }
		
		#region Master
        public async Task<List<Master>> GetMastersAsync()
        {
            List<Master> result ;
            try
            {
                _connection.Open();
                result = (await _connection.QueryAsync<Master>(MasterQueries.GetMaster)).ToList();
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.GetMasterAsync:", ex);
            }
            return result;
        }
		
        public async Task InsertMasterAsync(Master master)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(MasterQueries.InsertMaster, master);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.InsertMasterAsync:", ex);
            }
        }
		
        public async Task UpdateMasterAsync(Master master)
        {
            try
            {
                _connection.Open();
                master.ModifyDate = DateTime.UtcNow;
                await _connection.ExecuteAsync(MasterQueries.UpdateMaster, master);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.UpdateMasterAsync:", ex);
            }
        }
		
		public async Task DeleteMasterAsync(Guid IdMaster)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(MasterQueries.DeleteMaster, new { IdMaster });
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.DeleteMasterAsync:", ex);
            }
        }
		#endregion
		
		#region Option
        public async Task<List<MasterOption>> GetMasterOptionAsync(Guid IdMaster)
        {
            List<MasterOption> result;
            try
            {
                result = (await _connection.QueryAsync<MasterOption>(MasterQueries.GetOptions,new {IdMaster})).ToList();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.GetMasterOptionAsync:", ex);
            }
            return result;
        }
		
        public async Task InsertOptionAsync(MasterOption[] option)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(MasterQueries.InsertOption, option);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.InsertOptionAsync:", ex);
            }
        }

        public async Task UpdateOptionAsync(MasterOption[] option)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(MasterQueries.UpdatetOption, option);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.UpdateOptionAsync:", ex);
            }
        }
		public async Task DeleteOptionsByIdMasterAsync(Guid IdMaster)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(MasterQueries.DeleteOptionByIdMaster, new { IdMaster });
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.DeleteOptionsByIdMasterAsync:", ex);
            }
        }
		public async Task DeleteOptionAsync(Guid IdOption)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(MasterQueries.DeleteOptionByIdOption, new {IdOption });
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD MasterData.DeleteOptionAsync:", ex);
            }
        }
		#endregion
    }
}
